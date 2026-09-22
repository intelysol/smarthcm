@props([
    'formId' => null,
])

<div x-data="{
    isDirty: false,
    showPrompt: false,
    pendingUrl: null,
    init() {
        const form = document.getElementById('{{ $formId }}') || document.querySelector('form');
        if (!form) return;

        form.addEventListener('input', () => { this.isDirty = true; });
        form.addEventListener('change', () => { this.isDirty = true; });
        form.addEventListener('submit', () => { this.isDirty = false; });

        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        document.querySelectorAll('a[href]').forEach(link => {
            link.addEventListener('click', (e) => {
                if (this.isDirty && !link.getAttribute('href').startsWith('#') && !link.getAttribute('href').startsWith('javascript:')) {
                    e.preventDefault();
                    this.pendingUrl = link.getAttribute('href');
                    this.showPrompt = true;
                }
            });
        });
    },
    discard() {
        this.isDirty = false;
        this.showPrompt = false;
        if (this.pendingUrl) {
            window.location.href = this.pendingUrl;
        }
    }
}">
    <!-- Unsaved Changes Modal Warning -->
    <div x-show="showPrompt" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
         role="alertdialog"
         aria-modal="true"
         aria-labelledby="unsaved-title"
         style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 max-w-sm w-full text-center">
            <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-triangle-exclamation text-xl" aria-hidden="true"></i>
            </div>
            <h3 id="unsaved-title" class="text-base font-bold text-slate-900 mb-1">You have unsaved changes</h3>
            <p class="text-xs text-slate-500 mb-6">Leaving this page now will discard the information you have entered. Are you sure you want to discard your changes?</p>
            <div class="flex items-center justify-center gap-3">
                <button type="button" @click="showPrompt = false" class="btn-primary text-xs">
                    Stay on Page
                </button>
                <button type="button" @click="discard()" class="btn-secondary text-xs text-rose-600 border-rose-200 hover:bg-rose-50">
                    Discard Changes
                </button>
            </div>
        </div>
    </div>
</div>
