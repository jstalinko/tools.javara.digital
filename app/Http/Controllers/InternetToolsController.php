<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;

class InternetToolsController extends Controller
{
    public function smtpTester()
    {
        return Inertia::render('tools/internet/SmtpTester');
    }

    public function testSmtp(Request $request)
    {
        $request->validate([
            'host' => 'required',
            'port' => 'required|numeric',
            'username' => 'nullable',
            'password' => 'nullable',
            'encryption' => 'nullable',
            'from_address' => 'required|email',
            'to_address' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ]);

        config([
            'mail.mailers.smtp_testing' => [
                'transport' => 'smtp',
                'host' => $request->host,
                'port' => $request->port,
                'encryption' => $request->encryption,
                'username' => $request->username,
                'password' => $request->password,
                'timeout' => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN', $request->getHost()),
            ],
            'mail.from.address' => $request->from_address,
            'mail.from.name' => 'JavaraDigital SMTP Tester',
        ]);

        try {
            Mail::mailer('smtp_testing')
                ->raw($request->message, function($msg) use($request) {
                    $msg->to($request->to_address)
                        ->subject($request->subject);
                });
            
            return response()->json([
                'success' => true, 
                'message' => 'Email sent successfully! Connection to SMTP server works.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to connect or send email: ' . $e->getMessage()
            ], 400);
        }
    }

    public function ipGeolocation()
    {
        return Inertia::render('tools/internet/IpGeolocation');
    }

    public function readEmailInbox()
    {
        return Inertia::render('tools/internet/ReadEmailInbox');
    }
}

