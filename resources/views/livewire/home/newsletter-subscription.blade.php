<div class="activites-info bg-white py-5 mt-5 wow fadeIn" data-wow-delay="0.1s" style="background-color: #ffffff !important;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center wow fadeIn" data-wow-delay="0.5s">
                <h1 class="display-6 mb-4" style="color: #0e0d0dff;">Subscribe to Our Newsletter</h1>
                <p class="mb-4" style="color: #7A7B7C;">Stay updated with our latest news, events, and impact stories. Join our community of changemakers!</p>
                
                <div class="position-relative w-100 mb-2">
                    <form wire:submit.prevent="subscribe">
                        <div class="input-group">
                            <input wire:model="email" class="form-control border-0 w-100 ps-4 pe-5" type="email" placeholder="Enter Your Email" style="height: 60px; border-radius: 30px; background: #fff;">
                            <button type="submit" class="btn shadow-none position-absolute top-0 end-0 mt-2 me-2" style="border-radius: 50%; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; z-index: 10; background: #3CC78F; border: none;">
                                <i class="fa fa-paper-plane text-white fs-5"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
                @error('email') <span class="small d-block mt-2" style="color: #dc3545;">{{ $message }}</span> @enderror
                
                @if($successMessage)
                    <div class="alert alert-success mt-3 rounded-pill" role="alert" style="background: #3CC78F; border: none; color: #fff;">
                        <i class="fa fa-check-circle me-2"></i> Thank you for subscribing! Please check your email for confirmation.
                    </div>
                @else
                    <p class="mb-0 mt-3" style="color: #7A7B7C;">Don't worry, we won't spam you with emails.</p>
                @endif
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('newsletter-subscribed', () => {
            setTimeout(() => {
                $wire.set('successMessage', false);
            }, 5000);
        });
    </script>
    @endscript
</div>
