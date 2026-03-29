<?php
use App\Http\Controllers\AiToolsController;
use App\Http\Controllers\TextToolsController;
use App\Http\Controllers\UtilToolsController;
use App\Http\Controllers\ImageToolsController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'ai'], function(){
    
    Route::get('/caption-generator' ,[AiToolsController::class,'captionGenerator'])->name('ai.caption-generator');
    Route::get('/product-description' ,[AiToolsController::class,'productDescription'])->name('ai.product-description');
});

Route::group(['prefix' => 'text'] , function(){
    Route::get('/word-counter' , [TextToolsController::class,'wordCounter'])->name('text.word-counter');
    Route::get('/password-generator' , [TextToolsController::class,'passwordGenerator'])->name('text.password-generator');
    Route::post('/password-generator/hash', [TextToolsController::class, 'hashPassword'])->name('text.password-generator.hash');
    Route::get('/json-formatter', [TextToolsController::class, 'jsonFormatter'])->name('text.json-formatter');
    Route::post('/json-formatter/convert-php', [TextToolsController::class, 'convertPhpToJson'])->name('text.json-formatter.convert-php');
    Route::get('/base64-encoder', [TextToolsController::class, 'base64Encoder'])->name('text.base64-encoder');
    Route::get('/notepad/{uuid?}', [TextToolsController::class,'notepad'])->name('text.notepad');
    Route::post('/notepad/save', [TextToolsController::class, 'saveNotepad'])->name('text.notepad.save');
    Route::get('/lorem-ipsum', [TextToolsController::class,'loremIpsum'])->name('text.lorem-ipsum');
    Route::get('/test-mengetik', [TextToolsController::class,'testMengetik'])->named('text.test-mengetik');
});


Route::group(['prefix' => 'utility'] , function(){
    Route::get('/tasbih-digital' , [UtilToolsController::class , 'tasbihDigital'])->name('utility.tasbih-digital');
    Route::get('/papan-skor' , [UtilToolsController::class , 'papanSkor'])->name('utility.papan-skor');
    Route::get('/kocok-dadu', [UtilToolsController::class ,'kocokDadu'])->name('utility.kocok-dadu');
});

Route::group(['prefix' => 'image'] , function(){
    Route::get('/qr-generator' , [ImageToolsController::class , 'qrGenerator'])->name('image.qr-generator');
    Route::get('/png-to-webp' , [ImageToolsController::class , 'pngToWebp'])->name('image.png-to-webp');
    Route::get('/jpg-to-webp' , [ImageToolsController::class , 'jpgToWebp'])->name('image.jpg-to-webp');
    Route::post('/jpg-to-webp/process', [ImageToolsController::class, 'processJpgToWebp'])->name('image.jpg-to-webp.process');
    Route::post('/png-to-webp/process', [ImageToolsController::class, 'processPngToWebp'])->name('image.png-to-webp.process');
    Route::get('/png-to-webp/download-zip/{id}', [ImageToolsController::class, 'downloadZip'])->name('image.png-to-webp.download-zip');
});