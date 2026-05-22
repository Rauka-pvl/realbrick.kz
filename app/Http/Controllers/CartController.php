<?php

namespace App\Http\Controllers;

use App\Mail\LeadSubmittedMail;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $lang = $this->detectLang($request);
        $items = collect((array) $request->session()->get('cart.items', []))->values();
        $totalQty = (int) $items->sum('qty');
        $totalAmount = (float) $items->sum(function ($item) {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $price = (float) ($item['price_value'] ?? 0);

            return $qty * $price;
        });

        return view('real-brick.cart.index', [
            'lang' => $lang,
            'items' => $items,
            'totalQty' => $totalQty,
            'totalAmount' => $totalAmount,
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['required'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:999'],
            'price_value' => ['nullable', 'numeric', 'min:0'],
            'price_currency' => ['nullable', 'string', 'max:10'],
        ]);

        $items = (array) $request->session()->get('cart.items', []);
        $this->mergeCartItem($items, $data);
        $request->session()->put('cart.items', $items);

        return back()->with('success', 'Товар добавлен в корзину');
    }

    public function addBatch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.slug' => ['nullable', 'string', 'max:255'],
            'items.*.image_url' => ['nullable', 'string', 'max:2048'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.price_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.price_currency' => ['nullable', 'string', 'max:10'],
        ]);

        $items = (array) $request->session()->get('cart.items', []);
        $added = 0;

        foreach ($data['items'] as $row) {
            $this->mergeCartItem($items, $row);
            $added++;
        }

        $request->session()->put('cart.items', $items);

        $updatedItems = collect($items)->values();
        $totalQty = (int) $updatedItems->sum('qty');
        $totalAmount = (float) $updatedItems->sum(function ($item) {
            $itemQty = max(1, (int) ($item['qty'] ?? 1));
            $itemPrice = (float) ($item['price_value'] ?? 0);

            return $itemQty * $itemPrice;
        });

        return response()->json([
            'ok' => true,
            'added' => $added,
            'total_qty' => $totalQty,
            'total_amount' => $totalAmount,
            'message' => $added > 0 ? 'Товары добавлены в корзину' : 'Не удалось добавить товары',
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'qty' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $id = (string) $data['id'];
        $qty = (int) $data['qty'];
        $items = (array) $request->session()->get('cart.items', []);

        $removed = false;
        if (isset($items[$id])) {
            if ($qty === 0) {
                unset($items[$id]);
                $removed = true;
            } else {
                $items[$id]['qty'] = $qty;
            }
            $request->session()->put('cart.items', $items);
        }

        if ($request->expectsJson()) {
            $updatedItems = collect((array) $request->session()->get('cart.items', []))->values();
            $totalQty = (int) $updatedItems->sum('qty');
            $totalAmount = (float) $updatedItems->sum(function ($item) {
                $itemQty = max(1, (int) ($item['qty'] ?? 1));
                $itemPrice = (float) ($item['price_value'] ?? 0);

                return $itemQty * $itemPrice;
            });
            $currentItem = collect($updatedItems)->firstWhere('id', $id);
            $currentQty = (int) ($currentItem['qty'] ?? 0);
            $itemAmount = (float) (($currentItem['price_value'] ?? 0) * $currentQty);

            return response()->json([
                'ok' => true,
                'removed' => $removed,
                'item_id' => $id,
                'item_qty' => $currentQty,
                'item_amount' => $itemAmount,
                'total_qty' => $totalQty,
                'total_amount' => $totalAmount,
            ]);
        }

        return back()->with('success', 'Корзина обновлена');
    }

    public function remove(Request $request): RedirectResponse
    {
        $id = (string) $request->input('id', '');
        $items = (array) $request->session()->get('cart.items', []);
        if ($id !== '' && isset($items[$id])) {
            unset($items[$id]);
            $request->session()->put('cart.items', $items);
        }

        return back()->with('success', 'Товар удален из корзины');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('cart.items');

        return back()->with('success', 'Корзина очищена');
    }

    public function submit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'comment' => ['nullable', 'string', 'max:1200'],
        ]);

        $items = collect((array) $request->session()->get('cart.items', []))
            ->values()
            ->all();

        if ($items === []) {
            return back()->with('success', 'Корзина пуста. Добавьте товары перед отправкой.');
        }

        $itemsText = $this->formatCartItemsForLead($items);
        $userComment = trim((string) ($data['comment'] ?? ''));
        $combinedComment = "Товары из корзины:\n".$itemsText;
        if ($userComment !== '') {
            $combinedComment = $userComment."\n\n".$combinedComment;
        }
        $combinedComment = mb_substr($combinedComment, 0, 2000);

        $lead = Lead::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'comment' => $combinedComment,
        ]);

        Mail::to('mr.redle3@gmail.com')->send(new LeadSubmittedMail($lead));

        $request->session()->forget('cart.items');

        return redirect()
            ->route('cart.index')
            ->with('success', 'Заявка отправлена. Мы скоро свяжемся с вами.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, array<string, mixed>>  $items
     */
    private function mergeCartItem(array &$items, array $data): void
    {
        $qty = max(1, (int) ($data['qty'] ?? 1));
        $id = (string) $data['id'];

        if (isset($items[$id])) {
            $items[$id]['qty'] = min(999, (int) $items[$id]['qty'] + $qty);
            $incomingImage = trim((string) ($data['image_url'] ?? ''));
            if ($incomingImage !== '' && trim((string) ($items[$id]['image_url'] ?? '')) === '') {
                $items[$id]['image_url'] = $incomingImage;
            }

            return;
        }

        $items[$id] = [
            'id' => $id,
            'name' => (string) $data['name'],
            'slug' => (string) ($data['slug'] ?? ''),
            'image_url' => (string) ($data['image_url'] ?? ''),
            'price_value' => isset($data['price_value']) ? (float) $data['price_value'] : null,
            'price_currency' => isset($data['price_currency']) ? (string) $data['price_currency'] : 'USD',
            'qty' => $qty,
        ];
    }

    private function detectLang(Request $request): string
    {
        return 'ru';
    }

    private function formatCartItemsForLead(array $items): string
    {
        $lines = [];
        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? 'Товар'));
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $price = (float) ($item['price_value'] ?? 0);
            $amount = $price * $qty;
            $lines[] = "- {$name} x {$qty}".($price > 0 ? " ({$price} USD, сумма {$amount} USD)" : '');
        }

        return implode("\n", $lines);
    }
}

