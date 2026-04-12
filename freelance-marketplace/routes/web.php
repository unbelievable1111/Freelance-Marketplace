<?php

use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderApproveController;
use App\Http\Controllers\OrderCommentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

#Auth
Auth::routes();

#HomeController
Route::get('/', [HomeController::class, 'index'])->name('home.index');

#ProfileController
Route::middleware('auth')->group(function ()
{
    Route::get('/profile',                          [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/avatar',                   [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::put('/profile/password',                 [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/profile/edit',                     [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/public-profile/{publicUser}',      [ProfileController::class, 'publicProfileOverview'])->name('public-profile.overview');
});

#BankAccountController
Route::middleware('auth')->group(function ()
{
    Route::get('/profile/bank-accounts', [BankAccountController::class, 'index'])->name('profile.bank-accounts');
    Route::delete('/profile/bank-accounts/delete/{bankAccount}', [BankAccountController::class, 'deleteCard'])->name('profile.bank-accounts.delete-card');
    Route::post('/profile/bank-accounts/create', [BankAccountController::class, 'createCard'])->name('profile.bank-accounts.create-card');
});

#TransactionController
Route::middleware('auth')->group(function ()
{
    Route::get('/profile/transactions/finance-operations', [TransactionController::class, 'index'])->name('profile.transactions.finance-operations');
    Route::get('/profile/transactions/history', [TransactionController::class, 'history'])->name('profile.transactions.history');
    Route::post('/profile/transactions/deposit', [TransactionController::class, 'deposit'])->name('profile.transactions.deposit');
    Route::post('/profile/transactions/withdraw', [TransactionController::class, 'withdraw'])->name('profile.transactions.withdraw');
});

#OrderController
Route::middleware('auth')->group(function ()
{
    Route::get('/orders/create-order', [OrderController::class, 'createOrder'])->name('order.create-order');
    Route::post('/orders/create-order', [OrderController::class, 'createOrder'])->name('order.create-order');
    Route::get('/my-orders', [OrderController::class, 'showMyOrders'])->name('order.show-orders');
    Route::get('/orders/{order}', [OrderController::class, 'showOrder'])->name('order.show-order');
    Route::post('/orders/{order}', [OrderController::class, 'editOrder'])->name('order.edit-order');
    Route::delete('/orders/delete-attachment/{attachment}', [OrderController::class, 'deleteAttachment'])->name('order.delete-attachment');
    Route::post('/orders/add-attachments/{order}', [OrderController::class, 'addAttachment'])->name('order.add-attachment');
    Route::patch('/orders/cancel-order/{order}', [OrderController::class, 'cancelOrder'])->name('order.cancel-order');
    Route::patch('/orders/complete-order/{order}', [OrderController::class, 'completeOrder'])->name('order.complete-order');
    Route::post('/orders/{order}/extend-deadline', [OrderController::class, 'extendDeadline'])->name('order.extend-deadline');
});

#OrderApproveController
Route::middleware('auth')->group(function ()
{
    Route::post('/orders/{order}/approve', [OrderApproveController::class, 'makeApprove'])->name('order.approve');
    Route::put('/orders/{order}/approve-update', [OrderApproveController::class, 'update'])->name('order.update-approve');
    Route::delete('/orders/{order}/approve-cancel', [OrderApproveController::class, 'cancel'])->name('order.cancel-approve');
    Route::post('/orders/{order}/approval-submit/{orderApproval}', [OrderApproveController::class, 'submit'])->name('order.approval-submit');
    Route::get('/proposals', [OrderApproveController::class, 'showProposals'])->name('order.show-proposals');
});

#OrderCommentController
Route::middleware('auth')->group(function ()
{
    Route::post('/orders/{order}/comments', [OrderCommentController::class, 'leaveComment'])->name('order.leave-comment');
});

#ReviewController
Route::middleware('auth')->group(function ()
{
    Route::post('/orders/{order}/leave-review', [ReviewController::class, 'leaveReview'])->name('order.leave-review');
    Route::delete('/reviews/{review}/delete', [ReviewController::class, 'delete'])->name('order.delete-review');
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
});