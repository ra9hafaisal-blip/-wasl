@php
    $formKind = old('kind', $formQuote?->kind ?? 'quote');
    $textLimit = $formKind === 'quote' ? 155 : 2000;
    $formTags = old('tags_input', implode('، ', $formQuote?->tags ?? []));
    $selectedLinks = old('related_quote_ids', $selectedRelatedQuoteIds ?? []);
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="kind-{{ $formId }}" class="label">نوع البطاقة</label>
        <select id="kind-{{ $formId }}" name="kind" class="input" required>
            <option value="quote" @selected($formKind === 'quote')>اقتباس</option>
            <option value="idea" @selected($formKind === 'idea')>فكرة</option>
            <option value="note" @selected($formKind === 'note')>ملاحظة</option>
        </select>
    </div>
    <div>
        <label for="category-{{ $formId }}" class="label">المجال</label>
        <select id="category-{{ $formId }}" name="category" class="input" required>
            @foreach ($categories as $categoryValue => $categoryLabel)
                <option value="{{ $categoryValue }}" @selected(old('category', $formQuote?->category ?? 'mathematics') === $categoryValue)>{{ $categoryLabel }}</option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <label for="text-{{ $formId }}" class="label">المحتوى</label>
    <textarea id="text-{{ $formId }}" name="text" rows="4" required minlength="3" maxlength="{{ $textLimit }}"
        placeholder="اقتباس أو فكرة تستحق الحفظ..." class="input resize-y">{{ old('text', $formQuote?->text ?? '') }}</textarea>
    <p class="mt-1 text-xs text-white/70">الاقتباس حتى 155 حرفًا، والفكرة أو الملاحظة حتى 2000 حرف.</p>
</div>

<div>
    <label for="source-{{ $formId }}" class="label">المصدر</label>
    <input id="source-{{ $formId }}" name="source" type="text" maxlength="255"
        value="{{ old('source', $formQuote?->source ?? '') }}" placeholder="اسم الكتاب أو المقال أو الرابط" class="input">
</div>

<div>
    <label for="note-{{ $formId }}" class="label">ملاحظتك الشخصية</label>
    <textarea id="note-{{ $formId }}" name="note" rows="2" maxlength="1000"
        placeholder="ما الذي لفت انتباهك؟" class="input resize-y">{{ old('note', $formQuote?->note ?? '') }}</textarea>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="tags-{{ $formId }}" class="label">وسوم</label>
        <input id="tags-{{ $formId }}" name="tags_input" type="text" maxlength="300" value="{{ $formTags }}"
            placeholder="مثل: احتمالات، تفكير نقدي" class="input">
        <p class="mt-1 text-xs text-white/70">افصلي بين الوسوم بفاصلة.</p>
    </div>
    <div>
        <label for="visibility-{{ $formId }}" class="label">من يستطيع رؤيتها؟</label>
        <select id="visibility-{{ $formId }}" name="visibility" class="input" required>
            <option value="public" @selected(old('visibility', $formQuote?->visibility ?? 'public') === 'public')>الجميع</option>
            <option value="private" @selected(old('visibility', $formQuote?->visibility ?? 'public') === 'private')>أنا فقط</option>
        </select>
    </div>
</div>

<div>
    <label for="links-{{ $formId }}" class="label">أفكار مرتبطة (اختياري، حتى 5)</label>
    <select id="links-{{ $formId }}" name="related_quote_ids[]" class="input min-h-28" multiple size="4">
        @foreach ($linkableQuotes as $linkableQuote)
            <option value="{{ $linkableQuote->id }}" @selected(in_array($linkableQuote->id, $selectedLinks))>
                {{ $linkableQuote->user_id === auth()->id() ? 'مكتبتي' : 'المجتمع' }} · {{ \Illuminate\Support\Str::limit($linkableQuote->text, 70) }}
            </option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-white/70">للاختيار المتعدد، استخدم Ctrl أو ⌘.</p>
</div>

<div>
    <label for="relation-{{ $formId }}" class="label">طبيعة الربط</label>
    <select id="relation-{{ $formId }}" name="relation_type" class="input">
        <option value="related" @selected(old('relation_type', 'related') === 'related')>مرتبطة بـ</option>
        <option value="supports" @selected(old('relation_type') === 'supports')>تدعم</option>
        <option value="challenges" @selected(old('relation_type') === 'challenges')>تتحدى</option>
        <option value="extends" @selected(old('relation_type') === 'extends')>توسّع</option>
    </select>
</div>
