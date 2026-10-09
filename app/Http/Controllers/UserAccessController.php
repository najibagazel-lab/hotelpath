<?php
namespace App\Http\Controllers;

use App\Models\{ContractTask, Platform, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAccessController extends Controller
{
    private function selectedPlatformIds(Request $request, array $data): array
    {
        $platformIds = $data['platform_ids'] ?? [];

        if ($request->boolean('sejour_access')) {
            $platformIds = array_merge(
                $platformIds,
                Platform::where('active', true)->where('platform_group', 'SEJOUR')->pluck('id')->all()
            );
        }

        return array_values(array_unique($platformIds));
    }

    public function index()
    {
        abort_unless(auth()->user()->role !== 'ENTRY_MANAGER', 403);
        return view('access.index', ['users' => User::whereIn('role', ['ENTRY_MANAGER', 'CONTRACTING'])->with('platforms')->orderBy('name')->get(), 'platforms' => Platform::where('active', true)->orderBy('name')->get()]);
    }

    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()->role !== 'ENTRY_MANAGER' && in_array($user->role, ['ENTRY_MANAGER', 'CONTRACTING'], true), 403);
        $data = $request->validate([
            'platform_ids' => 'nullable|array',
            'platform_ids.*' => 'exists:platforms,id',
            'sejour_access' => 'nullable|boolean',
            'password' => 'nullable|string',
        ]);
        $selectedPlatformIds = $this->selectedPlatformIds($request, $data);
        $previousPlatformIds = $user->platforms()->pluck('platforms.id')->all();
        $user->platforms()->sync($user->role === 'ENTRY_MANAGER' ? $selectedPlatformIds : []);
        // An access change also assigns existing contract tasks. Otherwise the
        // checkbox would remain locked even though the employee has the access.
        $selectedPlatformIds = $user->role === 'ENTRY_MANAGER' ? $selectedPlatformIds : [];
        if ($selectedPlatformIds) {
            ContractTask::whereIn('platform_id', $selectedPlatformIds)->update(['assigned_user_id' => $user->id]);
        }
        $removedPlatformIds = array_diff($previousPlatformIds, $selectedPlatformIds);
        if ($removedPlatformIds) {
            ContractTask::where('assigned_user_id', $user->id)->whereIn('platform_id', $removedPlatformIds)->update(['assigned_user_id' => null]);
        }
        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }
        return back()->with('success', "Access updated for {$user->name}.");
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->role !== 'ENTRY_MANAGER', 403);
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|alpha_dash|unique:users,username',
            'password' => 'required|string',
            'role' => 'required|in:ENTRY_MANAGER,CONTRACTING',
            'platform_ids' => 'nullable|array',
            'platform_ids.*' => 'exists:platforms,id',
            'sejour_access' => 'nullable|boolean',
        ]);
        $selectedPlatformIds = $this->selectedPlatformIds($request, $data);
        $user = User::create(['name' => $data['name'], 'username' => $data['username'], 'email' => $data['username'].'@beslimetravel.local', 'password' => Hash::make($data['password']), 'role' => $data['role'], 'active' => true]);
        $user->platforms()->sync($data['role'] === 'ENTRY_MANAGER' ? $selectedPlatformIds : []);
        if ($data['role'] === 'ENTRY_MANAGER' && ! empty($selectedPlatformIds)) {
            ContractTask::whereIn('platform_id', $selectedPlatformIds)->update(['assigned_user_id' => $user->id]);
        }
        return back()->with('success', "Employee {$user->name} was added.");
    }
}
