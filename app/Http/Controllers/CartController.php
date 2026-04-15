<?php

namespace App\Http\Controllers;

use App\Mail\LeadSubmittedMail;
use App\Models\Lead;
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

        return view('real-brick.cart.index', [
            'lang' => $lang,
            'items' => $items,
            'totalQty' => $totalQty,
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
            'lang' => ['nullable', 'in:ru,kz'],
        ]);

        $qty = (int) ($data['qty'] ?? 1);
        $id = (string) $data['id'];
        $items = (array) $request->session()->get('cart.items', []);

        if (isset($items[$id])) {
            $items[$id]['qty'] = min(999, (int) $items[$id]['qty'] + $qty);
        } else {
            $items[$id] = [
                'id' => $id,
                'name' => $data['name'],
                'slug' => (string) ($data['slug'] ?? ''),
                'image_url' => (string) ($data['image_url'] ?? ''),
                'qty' => $qty,
            ];
        }

        $request->session()->put('cart.items', $items);

        return back()->with('success', 'Товар добавлен в корзину');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['required'],
            'qty' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $id = (string) $data['id'];
        $qty = (int) $data['qty'];
        $items = (array) $request->session()->get('cart.items', []);

        if (isset($items[$id])) {
            if ($qty === 0) {
                unset($items[$id]);
            } else {
                $items[$id]['qty'] = $qty;
            }
            $request->session()->put('cart.items', $items);
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
            ->route('cart.index', ['lang' => $this->detectLang($request)])
            ->with('success', 'Заявка отправлена. Мы скоро свяжемся с вами.');
    }

    private function detectLang(Request $request): string
    {
        $lang = strtolower((string) $request->query('lang', 'ru'));

        return in_array($lang, ['ru', 'kz'], true) ? $lang : 'ru';
    }

    private function formatCartItemsForLead(array $items): string
    {
        $lines = [];
        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? 'Товар'));
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $lines[] = "- {$name} x {$qty}";
        }

        return implode("\n", $lines);
    }
}

