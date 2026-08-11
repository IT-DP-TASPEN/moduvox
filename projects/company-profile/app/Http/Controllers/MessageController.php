<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ], [
            'name.required'    => 'Mohon masukkan nama lengkap Anda.',
            'email.required'   => 'Mohon masukkan alamat email Anda.',
            'email.email'      => 'Format email tidak valid.',
            'subject.required' => 'Mohon pilih atau masukkan cakupan pekerjaan.',
            'message.required' => 'Mohon sertakan uraian kebutuhan proyek Anda.',
        ]);

        Message::create($request->only('name', 'email', 'subject', 'message'));

        return redirect()->back()->with('success', 'Pesan Anda telah berhasil terkirim. Tim kami akan segera meninjau dan menghubungi Anda kembali.');
    }

    // Admin
    public function index()
    {
        $messages = Message::latest()->paginate(15);
        return view('admin.messages.index', compact('messages'));
    }

    public function show(Message $message)
    {
        $message->update(['is_read' => true]);
        return view('admin.messages.show', compact('message'));
    }

    public function destroy(Message $message)
    {
        $message->delete();
        return redirect()->route('admin.messages.index')->with('success', 'Data pesan berhasil dihapus.');
    }
}
