<?php

namespace App\Livewire\Navbar;

use App\Models\Batch;
use App\Models\CashRegister;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class TopNavbar extends Component
{
    // Profile Edit Modal
    public bool $showProfileModal = false;

    public string $name = '';

    public string $email = '';

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPassword_confirmation = '';

    public ?string $profileSuccess = null;

    public ?string $profileError = null;

    public function mount()
    {
        $user = Auth::user();
        if ($user) {
            $this->name = $user->name ?? '';
            $this->email = $user->email ?? '';
        }
    }

    public function openProfileModal()
    {
        $user = Auth::user();
        if ($user) {
            $this->name = $user->name ?? '';
            $this->email = $user->email ?? '';
        }
        $this->currentPassword = '';
        $this->newPassword = '';
        $this->newPassword_confirmation = '';
        $this->profileSuccess = null;
        $this->profileError = null;
        $this->showProfileModal = true;
    }

    public function updateProfile()
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $this->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
        ]);

        $user->name = $this->name;
        $user->email = $this->email;

        // If user wants to change password
        if (! empty($this->newPassword)) {
            $this->validate([
                'currentPassword' => 'required|string',
                'newPassword' => ['required', 'string', 'min:6', 'confirmed'],
            ]);

            if (! Hash::check($this->currentPassword, $user->password)) {
                $this->profileError = 'Current password does not match.';

                return;
            }

            $user->password = Hash::make($this->newPassword);
        }

        $user->save();

        $this->currentPassword = '';
        $this->newPassword = '';
        $this->newPassword_confirmation = '';
        $this->profileSuccess = 'Profile and credentials updated successfully!';
    }

    public function render()
    {
        $now = Carbon::now();
        $in30Days = Carbon::now()->addDays(30);

        // Low stock count
        $lowStockCount = Product::where('status', 'active')
            ->get()
            ->filter(fn ($p) => $p->totalStockPieces() <= ($p->min_stock_alert ?? 10))
            ->count();

        // Near-expiry batches
        $nearExpiryCount = Batch::where('current_stock', '>', 0)
            ->where('expiry_date', '>=', $now)
            ->where('expiry_date', '<=', $in30Days)
            ->count();

        // Expired batches
        $expiredCount = Batch::where('current_stock', '>', 0)
            ->where('expiry_date', '<', $now)
            ->count();

        // Active shift
        $activeShift = CashRegister::where('user_id', Auth::id())
            ->where('status', 'OPEN')
            ->first();

        // Total notifications
        $totalAlerts = ($lowStockCount > 0 ? 1 : 0) + ($nearExpiryCount > 0 ? 1 : 0) + ($expiredCount > 0 ? 1 : 0) + (! $activeShift ? 1 : 0);

        return view('livewire.navbar.top-navbar', [
            'lowStockCount' => $lowStockCount,
            'nearExpiryCount' => $nearExpiryCount,
            'expiredCount' => $expiredCount,
            'activeShift' => $activeShift,
            'totalAlerts' => $totalAlerts,
            'user' => Auth::user(),
        ]);
    }
}
