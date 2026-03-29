<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
class TextToolsController extends Controller
{
    public function loremIpsum(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/text/LoremIpsum');
    }


    public function notepad(Request $request, $uuid = null): \Inertia\Response
    
    {
        $note = null;
        if ($uuid) {
            $note = \App\Models\Notepad::where('uuid', $uuid)->first();
            if (!$note) {
                abort(404);
            }
        }
        return Inertia::render('tools/text/Notepad', ['note' => $note]);
    }
    public function saveNotepad(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'title' => 'nullable|string|max:255',
        ]);

        $uuid = (string) Str::uuid();
        \App\Models\Notepad::create([
            'uuid' => $uuid,
            'title' => $request->input('title') ?? 'Untitled',
            'content' => $request->input('content')
        ]);

        return response()->json(['uuid' => $uuid]);
    }

    public function base64Encoder(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/text/base64Encoder');
    }

    public function jsonFormatter(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/text/JsonFormatter');
    }
    public function passwordGenerator(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/text/PasswordGenerator');
    }
    public function testMengetik(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/text/TestMengetik');
    }
    public function hashPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'algorithm' => 'required|string|in:bcrypt,wp,md5,sha1,sha256,argon2id',
        ]);

        $password = $request->input('password');
        $algorithm = $request->input('algorithm');
        $result = '';

        switch ($algorithm) {
            case 'bcrypt':
                $result = \Illuminate\Support\Facades\Hash::make($password);
                break;
            case 'wp':
                // Simple phpass-like WordPress hash implementation
                // Using a basic random salt for WordPress Portable hash format ($P$)
                $result = $this->wordpressHash($password);
                break;
            case 'md5':
                $result = md5($password);
                break;
            case 'sha1':
                $result = sha1($password);
                break;
            case 'sha256':
                $result = hash('sha256', $password);
                break;
            case 'argon2id':
                $result = \Illuminate\Support\Facades\Hash::make($password, ['driver' => 'argon2id']);
                break;
        }

        return response()->json(['hash' => $result]);
    }

    private function wordpressHash($password)
    {
        // Simple implementation of phpass for WordPress ($P$)
        // In a real WP environment, this is 8 iterations with a random salt
        // Here we'll use bitwise operations and MD5 as per phpass spec
        $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $count_log2 = 8;
        $salt = substr(str_shuffle($itoa64), 0, 8);
        
        $count = 1 << $count_log2;
        $hash = md5($salt . $password, true);
        do {
            $hash = md5($hash . $password, true);
        } while (--$count);

        $output = '$P$' . $itoa64[$count_log2] . $salt;
        
        // Encode the hash
        $i = 0;
        while ($i < 16) {
            $value = ord($hash[$i++]);
            $output .= $itoa64[$value & 0x3f];
            if ($i < 16) {
                $value |= ord($hash[$i++]) << 8;
            }
            $output .= $itoa64[($value >> 6) & 0x3f];
            if ($i >= 16) {
                break;
            }
            $value |= ord($hash[$i++]) << 16;
            $output .= $itoa64[($value >> 12) & 0x3f];
            if ($i >= 16) {
                break;
            }
            $output .= $itoa64[($value >> 18) & 0x3f];
        }

        return $output;
    }
    public function convertPhpToJson(Request $request)
    {
        $request->validate([
            'phpArray' => 'required|string',
        ]);

        $phpArrayStr = trim($request->input('phpArray'));
        
        // Remove trailing semicolon if exists
        $phpArrayStr = rtrim($phpArrayStr, ';');
        
        // Basic conversion logic (best effort without eval)
        // 1. Convert array(...) to [...]
        $converted = preg_replace_callback('/array\s*\((.*?)\)/s', function($matches) {
            return '[' . $matches[1] . ']';
        }, $phpArrayStr);

        // 2. Convert PHP array keys (e.g., 'key' => or "key" =>) to JSON keys ("key":)
        $converted = preg_replace('/(\'|")?([a-zA-Z0-9_\-]+)(\'|")?\s*=>\s*/', '"$2": ', $converted);
        
        // 3. Convert single quotes to double quotes for strings
        $converted = preg_replace("/'([^'\\\\]*(?:\\\\.[^'\\\\]*)*)'/", '"$1"', $converted);
        
        // 4. Handle trailing commas
        $converted = preg_replace('/,\s*([\]\}])/', '$1', $converted);

        // Try to decode
        $decoded = json_decode($converted, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return response()->json(['json' => json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)]);
        }

        return response()->json([
            'error' => 'Failed to convert to valid JSON. Error: ' . json_last_error_msg(),
            'attempted' => $converted
        ], 422);
    }

    public function wordCounter(Request $request)
    {
        return Inertia::render('tools/text/WordCounter');
    }
}
