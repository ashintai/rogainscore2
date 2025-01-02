<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    /**
     * 
     * PAGE EXPIRED を防ぐための処理 
     * 
     * 
     */
    public function render($request, Exception $exception)
    {
        // 追加2
        //エラー画面をユーザーに見せる必要はないので、ログイン画面にリダイレクトさせる
        if ($exception instanceof TokenMismatchException) {
            return redirect('/login');
        }

        return parent::render($request, $exception);
    }


}
