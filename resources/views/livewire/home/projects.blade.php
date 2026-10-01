<!-- Carousel Start -->
<div class="container-fluid p-0 wow fadeIn" data-wow-delay="0.1s">
    <div class="owl-carousel header-carousel py-5">
        @forelse($projects as $project)
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="carousel-text">
                        <h1 class="display-1 text-uppercase mb-3">{{ $project->project_title }}</h1>
                        <p class="fs-5 mb-5">{{ $project->project_description }}</p>
                        <div class="d-flex">
                            <a class="btn btn-secondary py-3 px-4" href="{{ route('project.single', $project->id) }}">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="carousel-img">
                        @if($project->icon_image_url)
                            <img class="img-fluid" src="{{ $project->icon_image_url }}" alt="{{ $project->project_title }}" style="max-height: 400px; width: auto; object-fit: contain;">
                        @else
                            <img class="img-fluid" src="{{ asset('img/carousel-1.jpg') }}" alt="{{ $project->project_title }}" style="max-height: 400px; width: auto; object-fit: contain;">
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <!-- Fallback content if no projects exist -->
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="carousel-text">
                        <h1 class="display-1 text-uppercase mb-3">Together for a Better Tomorrow</h1>
                        <p class="fs-5 mb-5">We believe in creating opportunities and empowering communities through education, healthcare, and sustainable development.</p>
                        <div class="d-flex">
                            <a class="btn btn-secondary py-3 px-4" href="#">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="carousel-img">
                        <img class="img-fluid" src="{{ asset('img/carousel-1.jpg') }}" alt="Image" style="max-height: 400px; width: auto; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="carousel-text">
                        <h1 class="display-1 text-uppercase mb-3">Together, We Can End Hunger</h1>
                        <p class="fs-5 mb-5">No one should go to bed hungry. Your support helps us bring smiles, hope, and a brighter future to those in need.</p>
                        <div class="d-flex mt-4">
                            <a class="btn btn-secondary py-3 px-4" href="#">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="carousel-img">
                        <img class="img-fluid" src="{{ asset('img/carousel-2.jpg') }}" alt="Image" style="max-height: 400px; width: auto; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
<!-- Carousel End -->
