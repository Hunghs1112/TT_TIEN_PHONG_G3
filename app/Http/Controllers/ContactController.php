<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index()
    {
        return Inertia::render('Contact');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20|regex:/^[0-9]{10}$/',
            'message' => 'nullable|string',
        ], [
            'name.required' => 'Họ và tên không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'phone.required' => 'Số điện thoại không được để trống.',
            'phone.regex' => 'Số điện thoại không đúng định dạng.',
        ]);

        Mail::to('contact.thuongna@gmail.com')->send(new ContactFormMail($validated));

        return back()->with('success', 'Thông tin liên hệ của bạn đã được gửi thành công!');
    }
}
