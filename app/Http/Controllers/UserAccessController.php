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
        $roles = auth()->user()->role === 'ADMIN'
            ? ['ADMIN', 'ENTRY_MANAGER', 'CONTRACTING']
            : ['ENTRY_MANAGER', 'CONTRACTING'];

        return view('access.index', ['users' => User::whereIn('role', $roles)->with('platforms')->orderBy('name')->get(), 'platforms' => Platform::where('active', true)->orderBy('name')->get()]);
    }

    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()->role !== 'ENTRY_MANAGER' && in_array($user->role, ['ADMIN', 'ENTRY_MANAGER', 'CONTRACTING'], true), 403);
        abort_if($user->role === 'ADMIN' && auth()->user()->role !== 'ADMIN', 403);
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
        $this->claimOrphanTasks($user, $selectedPlatformIds);
        $removedPlatformIds = array_diff($previousPlatformIds, $selectedPlatformIds);
        foreach ($removedPlatformIds as $platformId) {
            // Hand the tasks back to another agent who still has this TO.
            $nextOwner = Platform::find($platformId)?->users()->where('active', true)->where('users.id', '!=', $user->id)->first();
            ContractTask::where('assigned_user_id', $user->id)->where('platform_id', $platformId)->update(['assigned_user_id' => $nextOwner?->id]);
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
            'role' => 'required|in:ADMIN,ENTRY_MANAGER,CONTRACTING',
            'platform_ids' => 'nullable|array',
            'platform_ids.*' => 'exists:platforms,id',
            'sejour_access' => 'nullable|boolean',
        ]);
        abort_if($data['role'] === 'ADMIN' && auth()->user()->role !== 'ADMIN', 403);
        $selectedPlatformIds = $this->selectedPlatformIds($request, $data);
        $user = User::create(['name' => $data['name'], 'username' => $data['username'], 'email' => $data['username'].'@beslimetravel.local', 'password' => Hash::make($data['password']), 'role' => $data['role'], 'active' => true]);
        $user->platforms()->sync($data['role'] === 'ENTRY_MANAGER' ? $selectedPlatformIds : []);
        if ($data['role'] === 'ENTRY_MANAGER') {
            $this->claimOrphanTasks($user, $selectedPlatformIds);
        }
        return back()->with('success', "Employee {$user->name} was added.");
    }

    /**
     * Give the user the tasks of these TOs that have no valid owner (unassigned,
     * or assigned to someone who no longer has the TO). Tasks already owned by
     * another agent with access are never taken over.
     */
    private function claimOrphanTasks(User $user, array $platformIds): void
    {
        foreach ($platformIds as $platformId) {
            $ownerIds = Platform::find($platformId)?->users()->where('active', true)->pluck('users.id')->all() ?? [];
            ContractTask::where('platform_id', $platformId)
                ->where(fn ($query) => $query->whereNull('assigned_user_id')->orWhereNotIn('assigned_user_id', $ownerIds))
                ->update(['assigned_user_id' => $user->id]);
        }
    }
}
