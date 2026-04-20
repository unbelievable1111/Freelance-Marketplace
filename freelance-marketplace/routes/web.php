<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderApproveController;
use App\Http\Controllers\OrderCommentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

#Auth
Auth::routes();

#HomeController
Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('home.pricing');
Route::get('/features', [HomeController::class, 'features'])->name('home.features');
Route::get('/faq', [HomeController::class, 'faq'])->name('home.faq');

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

#ChatController
Route::middleware('auth')->group(function ()
{
    Route::get('/chats',                            [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chats/{chat}',                     [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chats/start/{order}/{receiver}',  [ChatController::class, 'startChat'])->name('chat.start-chat');
    Route::post('/chats/{chat}/send-message',       [ChatController::class, 'sendMessage'])->name('chat.send-message');
    Route::get('/chat/{chat}/new-messages',         [ChatController::class, 'getNewMessages'])->name('chat.new-messages');
    Route::get('/chat/{chat}/older-messages',       [ChatController::class, 'getOlderMessages'])->name('chat.older-messages');
    Route::get('/chat-statuses/unread-status',      [ChatController::class, 'getUnreadStatus'])->name('chat.unread-status');
});

#NotificationController
Route::middleware('auth')->group(function ()
{
    Route::get('/notifications',                    [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count',       [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
});

#ReportController
Route::middleware('auth')->group(function ()
{
    Route::get('/reports',                              [ReportController::class, 'index'])->name('report.index');
    Route::get('/reports/create/{order}',               [ReportController::class, 'create'])->name('report.create');
    Route::post('/reports/create/{order}',              [ReportController::class, 'store'])->name('report.store');
    Route::get('/reports/show/{report}',                [ReportController::class, 'show'])->name('report.show');
    Route::post('/reports/{report}/comments',           [ReportController::class, 'storeComment'])->name('report.comment.store');
    Route::patch('/reports/{report}/complete',          [ReportController::class, 'complete'])->name('report.complete');
});