<?php
namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BankAccountController extends Controller
{
    /**
     * Display listing of bank accounts
     */
    public function index()
    {
        $bankAccounts = BankAccount::ordered()->get();

        return Inertia::render('Dashboard/Settings/BankAccounts', [
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Create form
     */
    public function create()
    {
        return Inertia::render('Dashboard/Settings/BankAccountForm', [
            'bankAccount' => null,
        ]);
    }

    /**
     * Edit form
     */
    public function edit(BankAccount $bankAccount)
    {
        return Inertia::render('Dashboard/Settings/BankAccountForm', [
            'bankAccount' => $bankAccount,
        ]);
    }

    /**
     * Store a new bank account
     */
    public function store(Request $request)
    {
        if (! $request->hasFile('logo')) {
            $request->request->remove('logo');
        }

        $validated = $request->validate([
            'bank_name'      => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name'   => 'required|string|max:100',
            'logo'           => 'nullable|image|mimes:png,jpg,jpeg,svg|max:1024',
            'is_active'      => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('bank-logos', 'public');
        }

        $validated['is_active']  = $request->boolean('is_active');
        $validated['sort_order'] = BankAccount::max('sort_order') + 1;

        BankAccount::create($validated);

        return redirect()
            ->route('settings.bank-accounts.index')
            ->with('success', 'Rekening bank berhasil ditambahkan.');
    }

    /**
     * Update bank account
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        if (! $request->hasFile('logo')) {
            $request->request->remove('logo');
        }

        $validated = $request->validate([
            'bank_name'      => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name'   => 'required|string|max:100',
            'logo'           => 'nullable|image|mimes:png,jpg,jpeg,svg|max:1024',
            'is_active'      => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            if ($bankAccount->logo) {
                Storage::disk('public')->delete($bankAccount->logo);
            }
            $validated['logo'] = $request->file('logo')->store('bank-logos', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');

        $bankAccount->update($validated);

        return redirect()
            ->route('settings.bank-accounts.index')
            ->with('success', 'Rekening bank berhasil diupdate.');
    }

    /**
     * Delete bank account
     */
    public function destroy(BankAccount $bankAccount)
    {
        // Check if used in transactions
        if ($bankAccount->transactions()->exists()) {
            return redirect()
                ->route('settings.bank-accounts.index')
                ->with('error', 'Rekening bank tidak bisa dihapus karena sudah digunakan di transaksi.');
        }

        // Delete logo
        if ($bankAccount->logo) {
            Storage::disk('public')->delete($bankAccount->logo);
        }

        $bankAccount->delete();

        return redirect()
            ->route('settings.bank-accounts.index')
            ->with('success', 'Rekening bank berhasil dihapus.');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(BankAccount $bankAccount)
    {
        $bankAccount->update([
            'is_active' => ! $bankAccount->is_active,
        ]);

        $status = $bankAccount->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('settings.bank-accounts.index')
            ->with('success', "Rekening {$bankAccount->bank_name} berhasil {$status}.");
    }

    /**
     * Update sort order
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:bank_accounts,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            BankAccount::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
