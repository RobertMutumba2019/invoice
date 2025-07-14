<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockDecrease;
use App\Models\EfrisGood;
use App\Services\EfrisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use Illuminate\Support\Facades\Storage;

class StockController extends Controller
{
    protected $efrisService;

    public function __construct(EfrisService $efrisService)
    {
        $this->efrisService = $efrisService;
    }

    // List available stock (approved)
    public function index()
    {
        $stocks = Stock::with(['creator', 'good'])
            ->approved()
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('stocks.index', compact('stocks'));
    }

    // List all stock (all statuses)
    public function allStock()
    {
        $stocks = Stock::with(['creator', 'good'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        $pendingCount = Stock::pending()->count();
        return view('stocks.all', compact('stocks', 'pendingCount'));
    }

    // List all stock decreases
    public function decreaseStock()
    {
        $stockDecreases = StockDecrease::with(['creator', 'good'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        $pendingCount = StockDecrease::pending()->count();
        return view('stocks.decrease', compact('stockDecreases', 'pendingCount'));
    }

    // Show form to create new stock
    public function create()
    {
        $goods = EfrisGood::orderBy('eg_name')->get();
        return view('stocks.create', compact('goods'));
    }

    // Show form to increase stock (edit)
    public function increaseStock($id)
    {
        $stock = Stock::with('good')->findOrFail($id);
        $goods = EfrisGood::orderBy('eg_name')->get();
        return view('stocks.increase', compact('stock', 'goods'));
    }

    // Show form to decrease stock (edit)
    public function decreaseStockForm($id)
    {
        $stockDecrease = StockDecrease::with('good')->findOrFail($id);
        $goods = EfrisGood::orderBy('eg_name')->get();
        return view('stocks.decrease-form', compact('stockDecrease', 'goods'));
    }

    // Store new stock
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sun_reference' => 'nullable|string|max:255',
            'item_code' => 'required|exists:efris_goods,eg_code',
            'quantity' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            DB::beginTransaction();
            $stock = Stock::create([
                'sun_reference' => $request->sun_reference,
                'item_code' => $request->item_code,
                'quantity' => $request->quantity,
                'remarks' => $request->remarks,
                'created_by' => Auth::id(),
            ]);
            activity()->performedOn($stock)->log('Stock increase created');
            DB::commit();
            return redirect()->route('stocks.all')->with('success', 'Stock increase created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating stock increase: ' . $e->getMessage())->withInput();
        }
    }

    // Update stock and push to EFRIS
    public function update(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            DB::beginTransaction();
            $stock->update([
                'quantity' => $request->quantity,
                'remarks' => $request->remarks,
                'updated_by' => Auth::id(),
            ]);
            if (!$stock->reference) {
                $response = $this->efrisService->pushStockToEfris($stock);
                if ($response['success']) {
                    $stock->update([
                        'reference' => $response['reference'],
                        'status' => 'approved'
                    ]);
                } else {
                    $stock->update(['status' => 'rejected']);
                    throw new \Exception($response['message']);
                }
            }
            activity()->performedOn($stock)->log('Stock increase updated and pushed to EFRIS');
            DB::commit();
            return redirect()->route('stocks.all')->with('success', 'Stock increase updated and pushed to EFRIS successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating stock: ' . $e->getMessage())->withInput();
        }
    }

    // Store new stock decrease
    public function storeDecrease(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sun_reference' => 'nullable|string|max:255',
            'item_code' => 'required|exists:efris_goods,eg_code',
            'quantity' => 'required|numeric|min:0.01',
            'decrease_reason' => 'required|string|max:255',
            'remarks' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            DB::beginTransaction();
            $stockDecrease = StockDecrease::create([
                'sun_reference' => $request->sun_reference,
                'item_code' => $request->item_code,
                'quantity' => $request->quantity,
                'decrease_reason' => $request->decrease_reason,
                'remarks' => $request->remarks,
                'created_by' => Auth::id(),
            ]);
            activity()->performedOn($stockDecrease)->log('Stock decrease created');
            DB::commit();
            return redirect()->route('stocks.decrease')->with('success', 'Stock decrease created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating stock decrease: ' . $e->getMessage())->withInput();
        }
    }

    // Update stock decrease and push to EFRIS
    public function updateDecrease(Request $request, $id)
    {
        $stockDecrease = StockDecrease::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:0.01',
            'decrease_reason' => 'required|string|max:255',
            'remarks' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            DB::beginTransaction();
            $stockDecrease->update([
                'quantity' => $request->quantity,
                'decrease_reason' => $request->decrease_reason,
                'remarks' => $request->remarks,
                'updated_by' => Auth::id(),
            ]);
            if (!$stockDecrease->reference) {
                $response = $this->efrisService->pushStockDecreaseToEfris($stockDecrease);
                if ($response['success']) {
                    $stockDecrease->update([
                        'reference' => $response['reference'],
                        'status' => 'approved'
                    ]);
                } else {
                    $stockDecrease->update(['status' => 'rejected']);
                    throw new \Exception($response['message']);
                }
            }
            activity()->performedOn($stockDecrease)->log('Stock decrease updated and pushed to EFRIS');
            DB::commit();
            return redirect()->route('stocks.decrease')->with('success', 'Stock decrease updated and pushed to EFRIS successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating stock decrease: ' . $e->getMessage())->withInput();
        }
    }

    // Show stock details
    public function show($id)
    {
        $stock = Stock::with(['creator', 'updater', 'good'])->findOrFail($id);
        return view('stocks.show', compact('stock'));
    }

    // Show stock decrease details
    public function showDecrease($id)
    {
        $stockDecrease = StockDecrease::with(['creator', 'updater', 'good'])->findOrFail($id);
        return view('stocks.show-decrease', compact('stockDecrease'));
    }

    // Delete stock
    public function destroy($id)
    {
        $stock = Stock::findOrFail($id);
        try {
            DB::beginTransaction();
            activity()->performedOn($stock)->log('Stock deleted');
            $stock->delete();
            DB::commit();
            return redirect()->route('stocks.all')->with('success', 'Stock deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error deleting stock: ' . $e->getMessage());
        }
    }

    // AJAX: Check stock quantity
    public function checkStockQuantity(Request $request)
    {
        $itemCode = $request->input('item_code');
        try {
            $response = $this->efrisService->checkStockQuantity($itemCode);
            return response()->json([
                'success' => true,
                'quantity' => $response['quantity'] ?? 0,
                'warning' => $response['warning'] ?? false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Get available stock for a good
    public function getAvailableStock($itemCode)
    {
        $stock = Stock::where('item_code', $itemCode)
            ->where('status', 'approved')
            ->sum('quantity');
        $decreases = StockDecrease::where('item_code', $itemCode)
            ->where('status', 'approved')
            ->sum('quantity');
        return $stock - $decreases;
    }

    /**
     * Display the QR code for a stock item.
     */
    public function showQrCode($id)
    {
        $stock = Stock::findOrFail($id);
        $filename = 'qrcodes/stock_' . $stock->id . '.png';
        if (!$stock->qrcode_path || !Storage::disk('public')->exists($stock->qrcode_path)) {
            $qrImage = \QrCode::format('png')->size(200)->generate($stock->item_code);
            Storage::disk('public')->put($filename, $qrImage);
            $stock->qrcode_path = $filename;
            $stock->save();
        }
        $qrUrl = asset('storage/' . $stock->qrcode_path);
        return view('stocks.qrcode', compact('stock', 'qrUrl'));
    }

    /**
     * Display the barcode for a stock item.
     */
    public function showBarcode($id)
    {
        $stock = Stock::findOrFail($id);
        $filename = 'barcodes/stock_' . $stock->id . '.png';
        if (!$stock->barcode_path || !Storage::disk('public')->exists($stock->barcode_path)) {
            $barcodeImage = \DNS1D::getBarcodePNG($stock->item_code, 'C128');
            Storage::disk('public')->put($filename, base64_decode($barcodeImage));
            $stock->barcode_path = $filename;
            $stock->save();
        }
        $barcodeUrl = asset('storage/' . $stock->barcode_path);
        return view('stocks.barcode', compact('stock', 'barcodeUrl'));
    }
}
