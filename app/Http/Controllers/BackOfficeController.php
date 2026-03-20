<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BackOfficeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 5);
        $query = User::query();

        $query->where('id', '!=', Auth::id());

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('firstname', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate($perPage)->withQueryString();

        return view('backoffice', compact('users', 'search', 'perPage'));
    }

    public function edit(User $user)
    {
        return view('edit-user', compact('user'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        session()->flash('toast_message', 'Utilisateur supprimé');
        session()->flash('toast_type', 'success');

        return redirect()->route('backoffice');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);

        $ids = array_filter($ids, fn($id) => $id != Auth::id());

        User::whereIn('id', $ids)->delete();

        session()->flash('toast_message', count($ids) . ' compte(s) supprimé(s)');
        session()->flash('toast_type', 'success');

        return redirect()->route('backoffice');
    }
}
