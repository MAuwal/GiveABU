<div id="make-donation" data-scroll-index="1" class="make_donation_area section_padding" style="background: linear-gradient(180deg, #f0f7f0 0%, #ffffff 60%); padding: 100px 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <div style="display:inline-flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #bbf7d0;padding:6px 18px;border-radius:30px;margin-bottom:16px;">
                    <span style="width:7px;height:7px;background:#227722;border-radius:50%;display:inline-block;animation:don-pulse 2s infinite;"></span>
                    <span style="font-size:0.72rem;font-weight:700;color:#15803d;text-transform:uppercase;letter-spacing:1.2px;">Make A Donation</span>
                </div>
                <h3 style="color:#1f2937;font-size:2.25rem;font-weight:800;margin-bottom:14px;font-family:'Playfair Display',serif;line-height:1.25;">
                    Support Our Mission
                </h3>
                <p style="color:#6b7280;font-size:1.05rem;max-width:520px;margin:0 auto;line-height:1.7;">
                    Your generous contribution helps us continue our work and make a lasting impact in the community.
                </p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-9">
                <div style="background:#fff;border-radius:24px;box-shadow:0 20px 60px rgba(0,0,0,0.08),0 2px 8px rgba(0,0,0,0.04);overflow:hidden;border:1px solid rgba(34,119,34,0.06);">

                    <!-- Green header band -->
                    <div style="background:linear-gradient(135deg,#227722 0%,#1a5c1a 100%);padding:26px 28px 34px;position:relative;text-align:center;">
                        <div style="width:50px;height:50px;background:rgba(255,255,255,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="rgba(255,255,255,0.3)" stroke="#fff"/>
                            </svg>
                        </div>
                        <h4 style="color:#fff;font-size:1.2rem;font-weight:700;margin:0 0 4px;font-family:'Playfair Display',serif;">Make a Donation</h4>
                        <p style="color:rgba(255,255,255,0.75);font-size:0.82rem;margin:0;">Every contribution creates lasting change</p>
                        <div style="position:absolute;bottom:-1px;left:0;right:0;line-height:0;">
                            <svg viewBox="0 0 400 18" preserveAspectRatio="none" style="display:block;width:100%;height:18px;">
                                <path d="M0,18 C100,0 300,0 400,18 L400,18 L0,18 Z" fill="#fff"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Form body -->
                    <div style="padding:28px 32px 32px;">
                        @if (session()->has('message'))
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:10px;padding:11px 15px;font-size:0.84rem;margin-bottom:18px;display:flex;align-items:center;gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            {{ session('message') }}
                        </div>
                        @endif
                        @if (session()->has('error'))
                        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:10px;padding:11px 15px;font-size:0.84rem;margin-bottom:18px;display:flex;align-items:center;gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#b91c1c" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ session('error') }}
                        </div>
                        @endif

                        <form wire:submit.prevent="donate" class="donation_form">

                            <!-- Email -->
                            <div style="margin-bottom:16px;">
                                <label style="display:block;font-size:0.73rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:7px;">Email Address <span style="color:#ef4444;">*</span></label>
                                <div class="mda-input-wrap">
                                    <span class="mda-prefix">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                    </span>
                                    <input type="email" wire:model="email" placeholder="you@example.com" required class="mda-input">
                                </div>
                                @error('email') <span style="color:#ef4444;font-size:0.76rem;margin-top:4px;display:block;">{{ $message }}</span> @enderror
                            </div>

                            <!-- Amount -->
                            <div style="margin-bottom:24px;">
                                <label style="display:block;font-size:0.73rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:7px;">Donation Amount <span style="color:#ef4444;">*</span></label>
                                <div class="mda-input-wrap">
                                    <span class="mda-prefix mda-prefix-currency">₦</span>
                                    <input type="number" min="100" step="1" wire:model="amount" placeholder="Enter amount (e.g. 5,000)" required class="mda-input mda-input-lg">
                                </div>
                                @error('amount') <span style="color:#ef4444;font-size:0.76rem;margin-top:4px;display:block;">{{ $message }}</span> @enderror
                            </div>

                            <!-- Divider -->
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                                <div style="flex:1;height:1px;background:#f3f4f6;"></div>
                                <span style="font-size:0.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1.5px;white-space:nowrap;">Choose payment method</span>
                                <div style="flex:1;height:1px;background:#f3f4f6;"></div>
                            </div>

                            <!-- Payment Cards -->
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px;">

                                {{-- Paystack - Commented out (testing mode only)
                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        wire:target="payWithPaystack"
                                        class="mda-gw-card mda-gw-paystack">
                                    <span wire:loading.remove wire:target="payWithPaystack" style="display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;">
                                        <div style="height:36px;display:flex;align-items:center;justify-content:center;">
                                            <img src="{{ asset('paystack.png') }}" alt="Paystack" style="height:40px;width:auto;max-width:130px;object-fit:contain;">
                                        </div>
                                    </span>
                                    <span wire:loading wire:target="payWithPaystack" class="mda-gw-loading">
                                        <svg class="mda-spin" width="14" height="14" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="10" stroke="#d1d5db" stroke-width="3"/>
                                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#374151" stroke-width="3" stroke-linecap="round"/>
                                        </svg>
                                        Processing…
                                    </span>
                                </button>
                                --}}

                                <!-- Squad -->
                                <button type="button"
                                        id="squad-pay-btn"
                                        wire:click="payWithSquad"
                                        wire:loading.attr="disabled"
                                        wire:target="payWithSquad"
                                        class="mda-gw-card mda-gw-squad">
                                    <span id="squad-btn-text" style="display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;">
                                        <div style="display:flex;align-items:center;justify-content:center;">
                                            <img src="{{ asset('GTCO-Squad-Hackathon-Program.jpg') }}" alt="Pay via Squad" class="mda-gw-img">
                                        </div>
                                    </span>
                                    <span id="squad-btn-loading" class="mda-gw-loading" style="display:none;">
                                        <svg class="mda-spin" width="14" height="14" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="10" stroke="#d1d5db" stroke-width="3"/>
                                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#374151" stroke-width="3" stroke-linecap="round"/>
                                        </svg>
                                        Redirecting…
                                    </span>
                                </button>

                                <!-- Interswitch -->
                                <button type="button"
                                        id="interswitch-pay-btn"
                                        wire:click="payWithInterswitch"
                                        wire:loading.attr="disabled"
                                        wire:target="payWithInterswitch"
                                        class="mda-gw-card mda-gw-interswitch">
                                    <span id="interswitch-btn-text" style="display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;">
                                        <div style="display:flex;align-items:center;justify-content:center;">
                                            <img src="{{ asset('interswitch2.jpg') }}" alt="Pay via Interswitch" class="mda-gw-img">
                                        </div>
                                    </span>
                                    <span id="interswitch-btn-loading" class="mda-gw-loading" style="display:none;">
                                        <svg class="mda-spin" width="14" height="14" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="10" stroke="#d1d5db" stroke-width="3"/>
                                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#2563eb" stroke-width="3" stroke-linecap="round"/>
                                        </svg>
                                        Redirecting…
                                    </span>
                                </button>
                            </div>

                            <!-- Trust bar -->
                            <div style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 0 2px;border-top:1px solid #f3f4f6;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <span style="font-size:0.68rem;color:#9ca3af;font-weight:500;">256-bit SSL · Squad · Interswitch</span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes don-pulse {
            0%,100% { opacity:1; transform:scale(1); }
            50% { opacity:0.5; transform:scale(0.85); }
        }

        .mda-input-wrap {
            display:flex;align-items:center;
            background:#f9fafb;border:1.5px solid #e5e7eb;
            border-radius:12px;overflow:hidden;
            transition:border-color 0.2s,box-shadow 0.2s,background 0.2s;
        }
        .mda-input-wrap:focus-within {
            border-color:#227722;background:#fff;
            box-shadow:0 0 0 3px rgba(34,119,34,0.08);
        }
        .mda-prefix {
            padding:0 12px;display:flex;align-items:center;flex-shrink:0;
        }
        .mda-prefix-currency { font-weight:800;color:#227722;font-size:1rem; }
        .mda-input {
            border:none;outline:none;background:transparent;
            height:50px;padding:0 12px 0 2px;
            font-size:0.93rem;color:#1f2937;font-weight:500;flex:1;min-width:0;
        }
        .mda-input-lg { font-weight:700;font-size:1.05rem; }

        /* Gateway cards */
        .mda-gw-card {
            background:#fff;border:2px solid #e5e7eb;border-radius:14px;
            padding:14px 10px;cursor:pointer;
            transition:all 0.25s cubic-bezier(0.4,0,0.2,1);
            display:flex;flex-direction:column;align-items:center;justify-content:center;
            min-height:85px;position:relative;overflow:hidden;
        }
        .mda-gw-card::after {
            content:'';position:absolute;inset:0;border-radius:12px;
            opacity:0;transition:opacity 0.25s ease;
        }
        .mda-gw-card:hover:not([disabled]) {
            transform:translateY(-4px) scale(1.02);
            box-shadow:0 12px 28px rgba(0,0,0,0.1);
        }
        .mda-gw-card:hover:not([disabled])::after { opacity:1; }
        .mda-gw-card:active:not([disabled]) {
            transform:translateY(-1px) scale(0.98);
            box-shadow:0 4px 12px rgba(0,0,0,0.08);
            transition-duration:0.1s;
        }
        .mda-gw-card[disabled] { opacity:0.5;cursor:not-allowed;filter:grayscale(0.4); }

        /* Gateway image styling */
        .mda-gw-img {
            height:48px;width:auto;max-width:140px;object-fit:contain;
            border-radius:6px;
            transition:transform 0.25s cubic-bezier(0.4,0,0.2,1),filter 0.25s ease;
            filter:brightness(1);
        }
        .mda-gw-card:hover:not([disabled]) .mda-gw-img {
            transform:scale(1.06);
            filter:brightness(1.05);
        }
        .mda-gw-card:active:not([disabled]) .mda-gw-img {
            transform:scale(0.96);
        }

        /* Individual gateway hover colors */
        .mda-gw-squad:hover:not([disabled]) {
            border-color:#00b8a9;
            box-shadow:0 0 0 3px rgba(0,184,169,0.12),0 12px 28px rgba(0,184,169,0.1);
        }
        .mda-gw-squad::after { background:linear-gradient(135deg,rgba(0,184,169,0.04),rgba(0,184,169,0.01)); }
        .mda-gw-interswitch:hover:not([disabled]) {
            border-color:#2563eb;
            box-shadow:0 0 0 3px rgba(37,99,235,0.12),0 12px 28px rgba(37,99,235,0.1);
        }
        .mda-gw-interswitch::after { background:linear-gradient(135deg,rgba(37,99,235,0.04),rgba(37,99,235,0.01)); }

        .mda-gw-loading {
            display:flex;align-items:center;gap:5px;
            font-size:0.72rem;color:#6b7280;font-weight:600;
        }
        @keyframes mda-spin { to { transform:rotate(360deg); } }
        .mda-spin { animation:mda-spin 0.8s linear infinite; }
    </style>

    <script>
        document.addEventListener('livewire:initialized', () => {

            Livewire.on('initiate-paystack', (data) => {
                const p = Array.isArray(data) ? data[0] : data;
                PaystackPop.setup({
                    key:      p.key,
                    email:    p.email,
                    amount:   p.amount,
                    currency: p.currency,
                    ref:      p.ref,
                    metadata: p.metadata,
                    onClose:  function () {},
                    callback: function (response) {
                        Livewire.dispatch('payment-success', { reference: response.reference });
                    },
                }).openIframe();
            });

            Livewire.on('initiate-squad', async (data) => {
                const p       = Array.isArray(data) ? data[0] : data;
                const btn     = document.getElementById('squad-pay-btn');
                const btnText = document.getElementById('squad-btn-text');
                const btnLoad = document.getElementById('squad-btn-loading');

                const showLoading = () => { if(btn) btn.disabled=true; if(btnText) btnText.style.display='none'; if(btnLoad) btnLoad.style.display='flex'; };
                const hideLoading = () => { if(btn) btn.disabled=false; if(btnText) btnText.style.display='flex'; if(btnLoad) btnLoad.style.display='none'; };

                showLoading();
                try {
                    const res = await fetch('/api/squad/pay', {
                        method:'POST',
                        headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                        body: JSON.stringify({ amount:p.amount, email:p.email, customer_name:p.customer_name||'' }),
                    });
                    const result = await res.json();
                    if (result.checkout_url) { window.location.href = result.checkout_url; }
                    else { alert(result.message || 'Unable to initiate Squad payment. Please try again.'); hideLoading(); }
                } catch (err) {
                    alert('A network error occurred. Please check your connection and try again.');
                    hideLoading();
                }
            });

            Livewire.on('initiate-interswitch', async (data) => {
                const p       = Array.isArray(data) ? data[0] : data;
                const btn     = document.getElementById('interswitch-pay-btn');
                const btnText = document.getElementById('interswitch-btn-text');
                const btnLoad = document.getElementById('interswitch-btn-loading');

                const showLoading = () => { if(btn) btn.disabled=true; if(btnText) btnText.style.display='none'; if(btnLoad) btnLoad.style.display='flex'; };
                const hideLoading = () => { if(btn) btn.disabled=false; if(btnText) btnText.style.display='flex'; if(btnLoad) btnLoad.style.display='none'; };

                showLoading();
                try {
                    const res = await fetch('/api/interswitch/pay', {
                        method:'POST',
                        headers:{
                            'Content-Type':'application/json',
                            'Accept':'application/json',
                            'X-Requested-With':'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({ 
                            amount: p.amount, 
                            email: p.email, 
                            customer_name: p.customer_name || '', 
                            callback_url: window.location.origin + '/api/interswitch/redirect' 
                        }),
                    });
                    const result = await res.json();
                    if (!res.ok) {
                        throw new Error(result.message || 'Unable to initialize Interswitch payment.');
                    }
                    if (!result.checkout_url || !result.payload) {
                        throw new Error('Interswitch did not return a valid payment URL.');
                    }

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = result.checkout_url;
                    form.style.display = 'none';
                    const allowedFields = [
                        'merchant_code', 'pay_item_id', 'txn_ref', 'amount', 'currency', 'site_redirect_url',
                        'cust_name', 'cust_email', 'cust_id', 'pay_item_name', 'mode',
                    ];

                    Object.entries(result.payload).forEach(([key, value]) => {
                        if (allowedFields.includes(key) && value !== undefined && value !== null) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            form.appendChild(input);
                        }
                    });
                    document.body.appendChild(form);
                    form.submit();
                } catch (error) {
                    alert(error.message || 'Failed to initiate Interswitch payment.');
                    hideLoading();
                }
            });

        });
    </script>
</div>
