@extends('layouts.app')

@section('title', 'ABU Endowment - Building a Better Tomorrow')

@section('content')
	<!-- Topbar Start -->
	<div class="container-fluid bg-secondary top-bar wow fadeIn" data-wow-delay="0.1s">
		<div class="row align-items-center h-100">
			<div class="col-lg-4 text-center text-lg-start">
				<a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none">
					<img src="{{ asset('abu_logo.png') }}" alt="ABU Logo" style="height: 50px; width: auto; margin-right: 10px;">
					<div>
						<h4 class="text-secondary m-0 fw-bold">ABU Endowment</h4>
						<small class="text-secondary m-0">& Crowd Funding</small>
                           </div>
				</a>
                        </div>
			<div class="col-lg-8 d-none d-lg-block">
				<div class="row">
					<div class="col-lg-4">
						<div class="d-flex justify-content-end">
							<div class="flex-shrink-0 btn-square bg-primary">
								<i class="fa fa-phone-alt text-dark"></i>
                    </div>
							<div class="ms-2">
								<h6 class="text-primary mb-0">Call Us</h6>
								<span class="text-white">+012 345 6789</span>
                </div>
                            </div>
                        </div>
					<div class="col-lg-4">
						<div class="d-flex justify-content-end">
							<div class="flex-shrink-0 btn-square bg-primary">
								<i class="fa fa-envelope-open text-dark"></i>
							</div>
							<div class="ms-2">
								<h6 class="text-primary mb-0">Mail Us</h6>
								<span class="text-white">info@domain.com</span>
							</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="d-flex justify-content-end">
							<div class="flex-shrink-0 btn-square bg-primary">
								<i class="fa fa-map-marker-alt text-dark"></i>
							</div>
							<div class="ms-2">
								<h6 class="text-primary mb-0">Address</h6>
								<span class="text-white">123 Street, NY, USA</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Topbar End -->

	<!-- Navbar Start -->
	<div class="container-fluid bg-secondary px-0 wow fadeIn" data-wow-delay="0.1s">
		<div class="nav-bar">
			<nav class="navbar navbar-expand-lg bg-primary navbar-dark px-4 py-lg-0">
				<h4 class="d-lg-none m-0">Menu</h4>
				<button type="button" class="navbar-toggler me-0" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
					<span class="navbar-toggler-icon"></span>
                            </button>
				<div class="collapse navbar-collapse" id="navbarCollapse">
					<div class="navbar-nav me-auto">
						<a href="{{ url('/') }}" class="nav-item nav-link active">Home</a>
						<div class="nav-item dropdown">
							<a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">About</a>
							<div class="dropdown-menu bg-primary">
								<a href="#about" class="dropdown-item">About Us</a>
								<a href="#mission" class="dropdown-item">Mission</a>
								<a href="#vision" class="dropdown-item">Vision</a>
								<a href="#board-of-trustees" class="dropdown-item">Board of Trustees</a>
								<a href="#investment-team" class="dropdown-item">Investment Team</a>
								<a href="#online-community" class="dropdown-item">Online Community</a>
                            </div>
                        </div>
						<a href="#services" class="nav-item nav-link">Services</a>
						<a href="#donation" class="nav-item nav-link">Donation</a>
						<a href="#news-events" class="nav-item nav-link">News & Events</a>

                </div>
					<div class="d-flex align-items-center ms-auto">
						<!-- Authentication Section -->
						<div id="authSection" class="d-flex align-items-center">
							<!-- Logged In User -->
							<div id="loggedInUser" class="d-none">
								<div class="nav-item dropdown">
									<a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" id="userDropdown">
										<i class="fas fa-user-circle me-2" style="font-size: 1.2rem;"></i>
										<span id="userDisplayName" class="d-none d-md-inline">User</span>
									</a>
									<div class="dropdown-menu dropdown-menu-end bg-primary">
										<div class="dropdown-item-text text-white">
											<small class="d-block text-muted">Logged in as</small>
											<strong id="dropdownUserName">User</strong>
										</div>
										<div class="dropdown-divider"></div>
										<a href="#" class="dropdown-item text-white" id="navbarLogoutBtn">
											<i class="fas fa-sign-out-alt me-2"></i> Logout
										</a>
									</div>
								</div>
							</div>
							
							<!-- Not Logged In -->
							<div id="notLoggedIn" class="d-flex align-items-center gap-2">
								<button class="btn btn-sm btn-outline-light" onclick="event.preventDefault(); $('#registrationModal').modal('show');" id="navbarRegisterBtn">
									<i class="fas fa-user-plus me-1"></i> <span class="d-none d-md-inline">Register</span>
								</button>
								<button class="btn btn-sm btn-light" onclick="event.preventDefault(); $('#loginModal').modal('show');" id="navbarLoginBtn">
									<i class="fas fa-sign-in-alt me-1"></i> <span class="d-none d-md-inline">Login</span>
								</button>
							</div>
						</div>
						
						<!-- Social Media Icons (Desktop Only) -->
						<div class="d-none d-lg-flex ms-2">
							<a class="btn btn-square btn-dark ms-2" href="#"><i class="fab fa-twitter"></i></a>
							<a class="btn btn-square btn-dark ms-2" href="#"><i class="fab fa-facebook-f"></i></a>
							<a class="btn btn-square btn-dark ms-2" href="#"><i class="fab fa-youtube"></i></a>
						</div>
            </div>
        </div>
    </nav>
		</div>
	</div>
	<!-- Navbar End -->


	<!-- Carousel Start -->
	@php
		$carouselProjects = \App\Models\Project::whereNotNull('project_title')
			->whereNotNull('project_description')
			->whereNotNull('icon_image')
			->orderBy('created_at', 'desc')
			->limit(5)
			->get();
	@endphp
	<div class="container-fluid p-0 wow fadeIn" data-wow-delay="0.1s">
		<div class="owl-carousel header-carousel py-5">
			@forelse($carouselProjects as $project)
				<div class="container py-5">
					<div class="row g-5 align-items-center">
						<div class="col-lg-6">
							<div class="carousel-text">
								<h1 class="display-1 text-uppercase mb-3">{{ Str::limit($project->project_title, 50) }}</h1>
								<p class="fs-5 mb-5">{{ Str::limit(strip_tags($project->project_description), 150) }}</p>
								<div class="d-flex">
									<a class="btn btn-primary py-3 px-4 me-3" href="#donation">Donate Now</a>
									<a class="btn btn-secondary py-3 px-4" href="#projects">Learn More</a>
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="carousel-img">
								@if($project->icon_image_url)
									<img class="w-100" src="{{ $project->icon_image_url }}" alt="{{ $project->project_title }}" style="max-height: 500px; width: auto; object-fit: cover;">
								@else
									<img class="w-100" src="{{ asset('img/carousel-1.jpg') }}" alt="{{ $project->project_title }}">
								@endif
							</div>
						</div>
					</div>
				</div>
			@empty
				{{-- Fallback content if no projects exist --}}
				<div class="container py-5">
					<div class="row g-5 align-items-center">
						<div class="col-lg-6">
							<div class="carousel-text">
								<h1 class="display-1 text-uppercase mb-3">Together for a Better Tomorrow</h1>
								<p class="fs-5 mb-5">We believe in creating opportunities and empowering communities through education, healthcare, and sustainable development.</p>
								<div class="d-flex">
									<a class="btn btn-primary py-3 px-4 me-3" href="#donation">Donate Now</a>
									<a class="btn btn-secondary py-3 px-4" href="#about">Join Us Now</a>
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="carousel-img">
								<img class="w-100" src="{{ asset('img/carousel-1.jpg') }}" alt="ABU Endowment">
							</div>
						</div>
					</div>
				</div>
				<div class="container py-5">
					<div class="row g-5 align-items-center">
						<div class="col-lg-6">
							<div class="carousel-text">
								<h1 class="display-1 text-uppercase mb-3">Building a Sustainable Future for ABU</h1>
								<p class="fs-5 mb-5">Your support helps us bring smiles, hope, and a brighter future to students, researchers, and communities in need.</p>
								<div class="d-flex mt-4">
									<a class="btn btn-primary py-3 px-4 me-3" href="#donation">Donate Now</a>
									<a class="btn btn-secondary py-3 px-4" href="#about">Learn More</a>
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="carousel-img">
								<img class="w-100" src="{{ asset('img/carousel-2.jpg') }}" alt="ABU Community">
							</div>
						</div>
					</div>
				</div>
			@endforelse
		</div>
	</div>
	<!-- Carousel End -->


	<!-- Video Start -->
	<div class="container-fluid bg-primary mb-5 wow fadeIn" data-wow-delay="0.1s">
		<div class="container">
			<div class="row g-0">
				<div class="col-lg-11">
					<div class="h-100 py-5 d-flex align-items-center">
						<button type="button" class="btn-play" data-bs-toggle="modal" data-src="https://www.youtube.com/embed/DWRcNpR6Kdc" data-bs-target="#videoModal">
							<span></span>
						</button>
						<h3 class="ms-5 mb-0">Together, we can build a world where everyone has the chance to thrive.</h3>
            </div>
				</div>
				<div class="d-none d-lg-block col-lg-1">
					<div class="h-100 w-100 bg-secondary d-flex align-items-center justify-content-center">
						<span class="text-white" style="transform: rotate(-90deg);">Scroll Down</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Video End -->

	<!-- Video Modal Start -->
	<div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content rounded-0">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Youtube Video</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
				<div class="modal-body">
					<!-- 16:9 aspect ratio -->
					<div class="ratio ratio-16x9">
						<iframe class="embed-responsive-item" src="" id="video" allowfullscreen allowscriptaccess="always" allow="autoplay"></iframe>
                    </div>
				</div>
			</div>
		</div>
	</div>
	<!-- Video Modal End -->

	<!-- About Start -->
	<div id="about" class="container-fluid py-5">
		<div class="container">
			<div class="row g-5 align-items-center">
				<div class="col-lg-6 wow fadeIn" data-wow-delay="0.2s">
					<div class="about-img">
						<img class="img-fluid w-100" src="{{ asset('img/About Endowment.png') }}" alt="Image">
                        </div>
                    </div>
				<div class="col-lg-6">
					<p class="section-title bg-white text-start text-primary pe-3">About Us</p>
					<h1 class="display-6 mb-4 wow fadeIn" data-wow-delay="0.2s">Join Hands, Change the World</h1>
					<p class="mb-4 wow fadeIn" data-wow-delay="0.3s">Ahmadu Bello University (ABU) Zaria, through its Endowment and Projects Crowdfunding initiative, provides a transparent and accessible platform where alumni, the general public, and well-wishers can contribute directly to the growth and development of the University.</p>
					<div class="row g-4 pt-2">
						<div class="col-sm-6 wow fadeIn" data-wow-delay="0.4s">
							<div class="h-100">
								<h3>Our Mission</h3>
								<p>Our mission is to create a sustainable future for Ahmadu Bello University by mobilizing collective support from alumni, friends, and partners through endowment donations and project-based crowdfunding.</p>
								<p class="text-dark"><i class="fa fa-check text-primary me-2"></i>Expanding opportunities for students through better infrastructure, welfare, and resources.</p>
								<p class="text-dark"><i class="fa fa-check text-primary me-2"></i>Ensuring that every contribution, no matter the size, plays a role in shaping the University’s legacy.</p>
								<p class="text-dark mb-0"><i class="fa fa-check text-primary me-2"></i>We can change someone’s life.</p>
							</div>
						</div>
						<div class="col-sm-6 wow fadeIn" data-wow-delay="0.5s">
							<div class="h-100 bg-primary p-4 text-center">
								<p class="fs-5 text-dark">Through your donations, we spread kindness and support to children and families.</p>
								<a class="btn btn-secondary py-2 px-4" href="#">Donate Now</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- About End -->


	<!-- Projects/Donation Section Start -->
	<div id="projects" class="container-fluid py-5">
		<div class="container">
			<div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 500px;">
				<p class="section-title bg-white text-center text-primary px-3">Our Projects</p>
				<h1 class="display-6 mb-4">Support Our Active Projects</h1>
			</div>
			@livewire('home.projects')
		</div>
	</div>
	<!-- Projects/Donation Section End -->


	<!-- Mission Start -->
	<div id="mission" class="container-fluid py-5 bg-light">
		<div class="container">
			<div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 800px;">
				<p class="section-title bg-white text-center text-primary px-3">Our Mission</p>
				<h1 class="display-6 mb-4">Building a Sustainable Future for ABU</h1>
                        </div>
			<div class="row g-5 align-items-center">
				<div class="col-lg-6 wow fadeIn" data-wow-delay="0.2s">
					<div class="h-100 p-4 bg-white rounded shadow-sm">
						<h3 class="text-primary mb-4"><i class="fa fa-bullseye me-2"></i>Mission Statement</h3>
						<p class="fs-5 mb-4">Our mission is to create a sustainable future for Ahmadu Bello University by mobilizing collective support from alumni, friends, and partners through endowment donations and project-based crowdfunding.</p>
						<div class="row g-4 mt-4">
							<div class="col-sm-6">
								<div class="d-flex align-items-start">
									<div class="btn-square bg-primary text-white me-3 flex-shrink-0">
										<i class="fa fa-graduation-cap"></i>
                    </div>
									<div>
										<h5>Student Support</h5>
										<p class="mb-0">Expanding opportunities through scholarships, infrastructure, and resources.</p>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="d-flex align-items-start">
									<div class="btn-square bg-primary text-white me-3 flex-shrink-0">
										<i class="fa fa-handshake"></i>
									</div>
									<div>
										<h5>Collective Impact</h5>
										<p class="mb-0">Every contribution, regardless of size, shapes the University's legacy.</p>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="d-flex align-items-start">
									<div class="btn-square bg-primary text-white me-3 flex-shrink-0">
										<i class="fa fa-flask"></i>
									</div>
									<div>
										<h5>Research Excellence</h5>
										<p class="mb-0">Funding innovative research that drives national and global development.</p>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="d-flex align-items-start">
									<div class="btn-square bg-primary text-white me-3 flex-shrink-0">
										<i class="fa fa-users"></i>
									</div>
									<div>
										<h5>Community Impact</h5>
										<p class="mb-0">Extending support beyond campus to uplift surrounding communities.</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-6 wow fadeIn" data-wow-delay="0.3s">
					<div class="h-100">
						<img class="img-fluid w-100 rounded shadow" src="{{ asset('img/About Endowment.png') }}" alt="ABU Endowment Mission">
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Mission End -->

	<!-- Vision Start -->
	<div id="vision" class="container-fluid py-5">
		<div class="container">
			<div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 800px;">
				<p class="section-title bg-white text-center text-primary px-3">Our Vision</p>
				<h1 class="display-6 mb-4">A World-Class University Through Collective Giving</h1>
                        </div>
			<div class="row g-5 align-items-center">
				<div class="col-lg-6 wow fadeIn" data-wow-delay="0.2s">
					<div class="h-100">
						<img class="img-fluid w-100 rounded shadow" src="{{ asset('img/About Endowment.png') }}" alt="ABU Endowment Vision">
                    </div>
                </div>
				<div class="col-lg-6 wow fadeIn" data-wow-delay="0.3s">
					<div class="h-100 p-4 bg-light rounded">
						<h3 class="text-primary mb-4"><i class="fa fa-eye me-2"></i>Vision Statement</h3>
						<p class="fs-5 mb-4">To become Nigeria's leading university endowment and crowdfunding platform, creating a sustainable ecosystem where alumni, donors, and partners collectively drive transformative change in education, research, and community development.</p>
						<div class="mt-4">
							<h5 class="text-primary mb-3">Our Vision Encompasses:</h5>
							<ul class="list-unstyled">
								<li class="mb-3"><i class="fa fa-check-circle text-primary me-2"></i><strong>Financial Sustainability:</strong> Building a robust endowment fund that ensures long-term financial stability for ABU's core operations and strategic initiatives.</li>
								<li class="mb-3"><i class="fa fa-check-circle text-primary me-2"></i><strong>Innovation Hub:</strong> Establishing ABU as a center of excellence in research, innovation, and technological advancement through funded projects.</li>
								<li class="mb-3"><i class="fa fa-check-circle text-primary me-2"></i><strong>Student Empowerment:</strong> Ensuring that financial barriers never limit access to quality education, through comprehensive scholarship and welfare programs.</li>
								<li class="mb-3"><i class="fa fa-check-circle text-primary me-2"></i><strong>Global Network:</strong> Connecting ABU alumni worldwide, fostering collaboration, mentorship, and continued engagement with the University.</li>
								<li class="mb-0"><i class="fa fa-check-circle text-primary me-2"></i><strong>Community Transformation:</strong> Creating lasting positive impact in communities through education, healthcare, and infrastructure development projects.</li>
							</ul>
            </div>
        </div>
    </div>
			</div>
		</div>
	</div>
	<!-- Vision End -->

	<!-- Board of Trustees Start -->
	<div id="board-of-trustees" class="container-fluid py-5 bg-light">
		<div class="container">
			<div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 800px;">
				<p class="section-title bg-white text-center text-primary px-3">Board of Trustees</p>
				<h1 class="display-6 mb-4">Governing Excellence in Endowment & Crowdfunding</h1>
				<p class="fs-5 mb-5">Our distinguished Board of Trustees brings together experienced leaders, alumni, and visionaries who guide the strategic direction of ABU's Endowment Fund and Crowdfunding initiatives.</p>
                </div>
			<div class="row g-4">
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
					<div class="team-item bg-white p-4 rounded shadow-sm h-100 text-center">
						<div class="team-img mb-4">
							<div class="btn-square bg-primary mx-auto mb-3" style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
								<i class="fa fa-user-tie fa-4x text-white"></i>
            </div>
                    </div>
						<h4>Prof. Kabiru Bala</h4>
						<span class="text-primary">Chairman, Board of Trustees</span>
						<p class="mt-3 mb-0">Vice-Chancellor, Ahmadu Bello University Zaria</p>
                </div>
            </div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.3s">
					<div class="team-item bg-white p-4 rounded shadow-sm h-100 text-center">
						<div class="team-img mb-4">
							<div class="btn-square bg-primary mx-auto mb-3" style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
								<i class="fa fa-user-graduate fa-4x text-white"></i>
        </div>
    </div>
						<h4>Alh. Muhammad Sanusi II</h4>
						<span class="text-primary">Trustee</span>
						<p class="mt-3 mb-0">Distinguished Alumni & Former CBN Governor</p>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
					<div class="team-item bg-white p-4 rounded shadow-sm h-100 text-center">
						<div class="team-img mb-4">
							<div class="btn-square bg-primary mx-auto mb-3" style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
								<i class="fa fa-user-shield fa-4x text-white"></i>
							</div>
						</div>
						<h4>Dr. Amina Mohammed</h4>
						<span class="text-primary">Trustee</span>
						<p class="mt-3 mb-0">Deputy Secretary-General, United Nations (Alumni)</p>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
					<div class="team-item bg-white p-4 rounded shadow-sm h-100 text-center">
						<div class="team-img mb-4">
							<div class="btn-square bg-primary mx-auto mb-3" style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
								<i class="fa fa-user-cog fa-4x text-white"></i>
							</div>
						</div>
						<h4>Engr. Ibrahim Musa</h4>
						<span class="text-primary">Trustee</span>
						<p class="mt-3 mb-0">Renowned Industrialist & ABU Alumni</p>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.3s">
					<div class="team-item bg-white p-4 rounded shadow-sm h-100 text-center">
						<div class="team-img mb-4">
							<div class="btn-square bg-primary mx-auto mb-3" style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
								<i class="fa fa-user-md fa-4x text-white"></i>
							</div>
						</div>
						<h4>Prof. Fatima Zara</h4>
						<span class="text-primary">Trustee</span>
						<p class="mt-3 mb-0">Dean, Faculty of Science & Research Expert</p>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
					<div class="team-item bg-white p-4 rounded shadow-sm h-100 text-center">
						<div class="team-img mb-4">
							<div class="btn-square bg-primary mx-auto mb-3" style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
								<i class="fa fa-user-tie fa-4x text-white"></i>
							</div>
						</div>
						<h4>Alh. Ibrahim Usman</h4>
						<span class="text-primary">Trustee</span>
						<p class="mt-3 mb-0">Finance Expert & Investment Advisor</p>
					</div>
				</div>
			</div>
			<div class="row mt-5">
				<div class="col-12">
					<div class="bg-primary p-5 rounded text-white text-center wow fadeIn" data-wow-delay="0.3s">
						<h3 class="mb-3">Board Responsibilities</h3>
						<div class="row g-4">
							<div class="col-md-4">
								<i class="fa fa-chart-line fa-3x mb-3"></i>
								<h5>Strategic Oversight</h5>
								<p class="mb-0">Setting long-term goals and strategic direction for endowment growth and project funding.</p>
							</div>
							<div class="col-md-4">
								<i class="fa fa-shield-alt fa-3x mb-3"></i>
								<h5>Financial Governance</h5>
								<p class="mb-0">Ensuring transparent, responsible management of funds and donor contributions.</p>
							</div>
							<div class="col-md-4">
								<i class="fa fa-balance-scale fa-3x mb-3"></i>
								<h5>Policy Development</h5>
								<p class="mb-0">Establishing policies that guide endowment investment and project selection criteria.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Board of Trustees End -->

	<!-- Investment Team Start -->
	<div id="investment-team" class="container-fluid py-5">
		<div class="container">
			<div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 800px;">
				<p class="section-title bg-white text-center text-primary px-3">Investment Team</p>
				<h1 class="display-6 mb-4">Expert Financial Stewardship for ABU Endowment</h1>
				<p class="fs-5 mb-5">Our Investment Team manages the ABU Endowment Fund with expertise, prudence, and a focus on sustainable growth to maximize long-term returns while supporting the University's mission.</p>
                </div>
			<div class="row g-5">
				<div class="col-lg-6 wow fadeIn" data-wow-delay="0.2s">
					<div class="h-100 bg-light p-5 rounded shadow-sm">
						<h3 class="text-primary mb-4"><i class="fa fa-chart-pie me-2"></i>Investment Strategy</h3>
						<p class="fs-5 mb-4">Our investment approach is designed to preserve capital while generating sustainable returns that support ABU's endowment goals.</p>
						<div class="mt-4">
							<h5 class="text-primary mb-3">Investment Principles:</h5>
							<ul class="list-unstyled">
								<li class="mb-3"><i class="fa fa-check-circle text-primary me-2"></i><strong>Diversification:</strong> Balanced portfolio across equities, fixed income, real estate, and alternative investments</li>
								<li class="mb-3"><i class="fa fa-check-circle text-primary me-2"></i><strong>Risk Management:</strong> Prudent risk assessment and mitigation strategies to protect donor contributions</li>
								<li class="mb-3"><i class="fa fa-check-circle text-primary me-2"></i><strong>Long-Term Focus:</strong> Investment decisions aligned with ABU's multi-generational mission</li>
								<li class="mb-3"><i class="fa fa-check-circle text-primary me-2"></i><strong>Ethical Investing:</strong> ESG (Environmental, Social, Governance) considerations in all investment decisions</li>
								<li class="mb-0"><i class="fa fa-check-circle text-primary me-2"></i><strong>Transparency:</strong> Regular reporting to donors and stakeholders on fund performance and allocation</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="col-lg-6 wow fadeIn" data-wow-delay="0.3s">
					<div class="h-100">
						<h3 class="text-primary mb-4"><i class="fa fa-users-cog me-2"></i>Investment Team Members</h3>
						<div class="row g-4">
							<div class="col-12">
								<div class="d-flex align-items-start bg-white p-4 rounded shadow-sm">
									<div class="btn-square bg-primary text-white me-3 flex-shrink-0" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
										<i class="fa fa-user-tie fa-2x"></i>
									</div>
                        <div>
										<h5 class="mb-1">Dr. Abdullahi Ibrahim</h5>
										<span class="text-primary">Chief Investment Officer</span>
										<p class="mb-0 mt-2">30+ years experience in portfolio management and institutional investing</p>
									</div>
								</div>
							</div>
							<div class="col-12">
								<div class="d-flex align-items-start bg-white p-4 rounded shadow-sm">
									<div class="btn-square bg-primary text-white me-3 flex-shrink-0" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
										<i class="fa fa-chart-line fa-2x"></i>
									</div>
									<div>
										<h5 class="mb-1">Hajiya Aisha Mohammed</h5>
										<span class="text-primary">Senior Portfolio Manager</span>
										<p class="mb-0 mt-2">Expertise in fixed income securities and risk management strategies</p>
									</div>
								</div>
							</div>
							<div class="col-12">
								<div class="d-flex align-items-start bg-white p-4 rounded shadow-sm">
									<div class="btn-square bg-primary text-white me-3 flex-shrink-0" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
										<i class="fa fa-balance-scale fa-2x"></i>
									</div>
									<div>
										<h5 class="mb-1">Engr. Hassan Bello</h5>
										<span class="text-primary">Investment Analyst</span>
										<p class="mb-0 mt-2">Specialized in equity research and alternative investment evaluation</p>
									</div>
								</div>
							</div>
							<div class="col-12">
								<div class="d-flex align-items-start bg-white p-4 rounded shadow-sm">
									<div class="btn-square bg-primary text-white me-3 flex-shrink-0" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
										<i class="fa fa-file-invoice-dollar fa-2x"></i>
									</div>
									<div>
										<h5 class="mb-1">Mr. John Okeke</h5>
										<span class="text-primary">Compliance & Reporting Officer</span>
										<p class="mb-0 mt-2">Ensuring regulatory compliance and transparent financial reporting</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row mt-5">
				<div class="col-12">
					<div class="bg-secondary text-white p-5 rounded text-center wow fadeIn" data-wow-delay="0.3s">
						<h3 class="mb-3"><i class="fa fa-chart-bar me-2"></i>Investment Performance</h3>
						<p class="fs-5 mb-4">Our commitment to excellence in investment management ensures sustainable growth of the ABU Endowment Fund, supporting scholarships, research, and infrastructure development for generations to come.</p>
						<a href="#donation" class="btn btn-primary btn-lg px-5 py-3">Contribute to Endowment Fund</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Investment Team End -->

	<!-- Online Community Start -->
	<div id="online-community" class="container-fluid py-5 bg-light">
		<div class="container">
			<div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 800px;">
				<p class="section-title bg-white text-center text-primary px-3">Online Community</p>
				<h1 class="display-6 mb-4">Connect, Engage, and Make a Difference Together</h1>
				<p class="fs-5 mb-5">Join ABU's vibrant online community of alumni, donors, students, and supporters. Connect with fellow alumni, stay updated on projects, and contribute to the University's growth through our digital platform.</p>
			</div>
			<div class="row g-5">
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
					<div class="service-item h-100 bg-white p-5 rounded shadow-sm text-center">
						<div class="btn-square bg-primary mx-auto mb-4" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
							<i class="fa fa-users fa-3x text-white"></i>
						</div>
						<h3 class="mb-3">Alumni Network</h3>
						<p class="mb-4">Reconnect with classmates, network with fellow graduates, and stay connected to your ABU community. Access exclusive alumni events, mentorship programs, and professional networking opportunities.</p>
						<a href="#" class="btn btn-primary">Join Network</a>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.3s">
					<div class="service-item h-100 bg-white p-5 rounded shadow-sm text-center">
						<div class="btn-square bg-primary mx-auto mb-4" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
							<i class="fa fa-hand-holding-heart fa-3x text-white"></i>
						</div>
						<h3 class="mb-3">Donor Dashboard</h3>
						<p class="mb-4">Track your donations, view impact reports, and see how your contributions are making a difference. Access detailed reports on projects you've supported and endowment fund performance.</p>
						<a href="#donation" class="btn btn-primary">View Dashboard</a>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
					<div class="service-item h-100 bg-white p-5 rounded shadow-sm text-center">
						<div class="btn-square bg-primary mx-auto mb-4" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
							<i class="fa fa-project-diagram fa-3x text-white"></i>
						</div>
						<h3 class="mb-3">Project Updates</h3>
						<p class="mb-4">Stay informed about funded projects through regular updates, photos, and progress reports. See your donations in action and understand the real-world impact of your contributions.</p>
						<a href="#services" class="btn btn-primary">Explore Projects</a>
					</div>
				</div>
			</div>
			<div class="row mt-5">
				<div class="col-lg-6 wow fadeIn" data-wow-delay="0.2s">
					<div class="bg-primary text-white p-5 rounded h-100">
						<h3 class="mb-4"><i class="fa fa-comments me-2"></i>Community Features</h3>
						<ul class="list-unstyled">
							<li class="mb-3"><i class="fa fa-check-circle me-2"></i><strong>Discussion Forums:</strong> Engage in meaningful conversations about ABU, education, and community development</li>
							<li class="mb-3"><i class="fa fa-check-circle me-2"></i><strong>Event Calendar:</strong> Never miss alumni gatherings, fundraisers, and University events</li>
							<li class="mb-3"><i class="fa fa-check-circle me-2"></i><strong>Mentorship Programs:</strong> Connect students with alumni mentors for career guidance and support</li>
							<li class="mb-3"><i class="fa fa-check-circle me-2"></i><strong>Volunteer Opportunities:</strong> Participate in community outreach and University service initiatives</li>
							<li class="mb-0"><i class="fa fa-check-circle me-2"></i><strong>Resource Library:</strong> Access educational materials, research publications, and University archives</li>
                            </ul>
                        </div>
				</div>
				<div class="col-lg-6 wow fadeIn" data-wow-delay="0.3s">
					<div class="bg-light p-5 rounded h-100">
						<h3 class="text-primary mb-4"><i class="fa fa-share-alt me-2"></i>Stay Connected</h3>
						<p class="fs-5 mb-4">Follow ABU Endowment on social media and join thousands of alumni and supporters in building a better future for the University.</p>
						<div class="d-flex flex-wrap gap-3 mb-4">
							<a href="#" class="btn btn-square btn-primary" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
								<i class="fab fa-facebook-f fa-2x"></i>
							</a>
							<a href="#" class="btn btn-square btn-primary" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
								<i class="fab fa-twitter fa-2x"></i>
							</a>
							<a href="#" class="btn btn-square btn-primary" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
								<i class="fab fa-linkedin-in fa-2x"></i>
							</a>
							<a href="#" class="btn btn-square btn-primary" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
								<i class="fab fa-instagram fa-2x"></i>
							</a>
							<a href="#" class="btn btn-square btn-primary" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
								<i class="fab fa-youtube fa-2x"></i>
							</a>
                        </div>
						<div class="bg-white p-4 rounded">
							<h5 class="text-primary mb-3">Get Started Today</h5>
							<p class="mb-3">Create your account to access all community features, track your donations, and connect with the ABU family worldwide.</p>
							<a href="#" class="btn btn-secondary w-100">Create Account</a>
                    </div>
                </div>
            </div>
			</div>
		</div>
	</div>
	<!-- Online Community End -->

	<!-- Service Start -->
	<div class="container-fluid py-5">
		<div class="container">
			<div class="row g-5">
				<div class="col-md-12 col-lg-4 col-xl-3 wow fadeIn" data-wow-delay="0.1s">
					<div class="service-title">
						<h1 class="display-6 mb-4">What We Do with Your Support.</h1>
						<p class="fs-5 mb-0">Through ABU Endowments, we build a stronger future for education, research, and community impact.</p>
					</div>
				</div>
				<div class="col-md-12 col-lg-8 col-xl-9">
					<div class="row g-5">
						<div class="col-sm-6 col-md-4 wow fadeIn" data-wow-delay="0.1s">
							<div class="service-item h-100">
								<div class="btn-square bg-light mb-4">
									<i class="fa fa-graduation-cap fa-2x text-secondary"></i>
								</div>
								<h3>Scholarships</h3>
								<p class="mb-2">We support bright students through scholarships, giving every talent the chance to shine at ABU.</p>
								<a href="#">Read More</a>
							</div>
						</div>
						<div class="col-sm-6 col-md-4 wow fadeIn" data-wow-delay="0.3s">
							<div class="service-item h-100">
								<div class="btn-square bg-light mb-4">
									<i class="fa fa-flask fa-2x text-secondary"></i>
								</div>
								<h3>Research & Innovation</h3>
								<p class="mb-2">We fund projects that drive discovery, research, and innovation to solve today’s real challenges.</p>
								<a href="#">Read More</a>
							</div>
						</div>
						<div class="col-sm-6 col-md-4 wow fadeIn" data-wow-delay="0.5s">
							<div class="service-item h-100">
								<div class="btn-square bg-light mb-4">
									<i class="fa fa-users fa-2x text-secondary"></i>
								</div>
								<h3>Student Welfare</h3>
								<p class="mb-2">Your donations improve student life — from health care to mentorship and career development.</p>
								<a href="#">Read More</a>
							</div>
						</div>
						<div class="col-sm-6 col-md-4 wow fadeIn" data-wow-delay="0.1s">
							<div class="service-item h-100">
								<div class="btn-square bg-light mb-4">
									<i class="fa fa-book-open fa-2x text-secondary"></i>
								</div>
								<h3>Learning Resources</h3>
								<p class="mb-2">We invest in libraries, digital tools, and facilities that make learning more impactful and accessible.</p>
								<a href="#">Read More</a>
							</div>
						</div>
						<div class="col-sm-6 col-md-4 wow fadeIn" data-wow-delay="0.3s">
							<div class="service-item h-100">
								<div class="btn-square bg-light mb-4">
									<i class="fa fa-chalkboard-teacher fa-2x text-secondary"></i>
								</div>
								<h3>Infrastructure Development</h3>
								<p class="mb-2">From classrooms to hostels, we build spaces that support learning, growth, and excellence.</p>
								<a href="#">Read More</a>
							</div>
						</div>
						<div class="col-sm-6 col-md-4 wow fadeIn" data-wow-delay="0.5s">
							<div class="service-item h-100">
								<div class="btn-square bg-light mb-4">
									<i class="fa fa-hand-holding-heart fa-2x text-secondary"></i>
								</div>
								<h3>Community Impact</h3>
								<p class="mb-2">Our projects extend beyond ABU, creating opportunities and solutions that uplift communities.</p>
								<a href="#">Read More</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Service End -->

	<!-- Features Start -->
	<div class="container-fluid py-5">
		<div class="container">
			<div class="row g-5 align-items-center">
				<div class="col-lg-6">
					<div class="rounded overflow-hidden">
						<div class="row g-0">
							<div class="col-sm-6 wow fadeIn" data-wow-delay="0.1s">
								<div class="text-center bg-primary py-5 px-4 h-100">
									<i class="fa fa-users fa-3x text-secondary mb-3"></i>
									<h1 class="display-5 mb-0" data-toggle="counter-up">5000</h1>
									<span class="text-dark">Alumni Members</span>
								</div>
							</div>
							<!-- <div class="col-sm-6 wow fadeIn" data-wow-delay="0.3s">
								<div class="text-center bg-secondary py-5 px-4 h-100">
									<i class="fa fa-award fa-3x text-primary mb-3"></i>
									<h1 class="display-5 text-white mb-0" data-toggle="counter-up">70</h1>
									<span class="text-white">Award Winning</span>
								</div>
							</div> -->
							<div class="col-sm-6 wow fadeIn" data-wow-delay="0.5s">
								<div class="text-center bg-secondary py-5 px-4 h-100">
									<i class="fa fa-list-check fa-3x text-primary mb-3"></i>
									<h1 class="display-5 text-white mb-0" data-toggle="counter-up">300</h1>
									<span class="text-white">Total Projects</span>
								</div>
							</div>
							<div class="col-sm-6 wow fadeIn" data-wow-delay="0.7s">
								<div class="text-center bg-primary py-5 px-4 h-100">
									<i class="fa fa-comments fa-3x text-secondary mb-3"></i>
									<h1 class="display-5 mb-0" data-toggle="counter-up">7000</h1>
									<span class="text-dark">Client's Review</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-6">
					<p class="section-title bg-white text-start text-primary pe-3">Why Us!</p>
					<h1 class="display-6 mb-4 wow fadeIn" data-wow-delay="0.2s">Few Reasons Why People Choose ABU Endowment!</h1>
					<p class="mb-4 wow fadeIn" data-wow-delay="0.3s">We believe in creating opportunities and empowering communities through education, healthcare, and sustainable development. Your support helps us bring smiles, hope, and a brighter future to those in need.</p>
					<p class="text-dark wow fadeIn" data-wow-delay="0.4s"><i class="fa fa-check text-primary me-2"></i>Justo magna erat amet</p>
					<p class="text-dark wow fadeIn" data-wow-delay="0.5s"><i class="fa fa-check text-primary me-2"></i>Aliqu diam amet diam et eos</p>
					<p class="text-dark wow fadeIn" data-wow-delay="0.6s"><i class="fa fa-check text-primary me-2"></i>Clita erat ipsum et lorem et sit</p>
					<div class="d-flex mt-4 wow fadeIn" data-wow-delay="0.7s">
						<a class="btn btn-primary py-3 px-4 me-3" href="#">Donate Now</a>
						<a class="btn btn-secondary py-3 px-4" href="#">Join Us Now</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Features End -->

	<!-- Donation Start -->

	<!-- <div class="container-fluid py-5">
		<div class="container">
			<div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 500px;">
				<p class="section-title bg-white text-center text-primary px-3">Donation</p>
				<h1 class="display-6 mb-4">Our Donation Causes Around the World</h1>
			</div>
			<div class="row g-4">
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
					<div class="donation-item d-flex h-100 p-4">
						<div class="donation-progress d-flex flex-column flex-shrink-0 text-center me-4">
							<h6 class="mb-0">Raised</h6>
							<span class="mb-2">$8000</span>
							<div class="progress d-flex align-items-end w-100 h-100 mb-2">
								<div class="progress-bar w-100 bg-secondary" role="progressbar" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">
									<span class="fs-4">85%</span>
								</div>
							</div>
							<h6 class="mb-0">Goal</h6>
							<span>$10000</span>
						</div>
						<div class="donation-detail">
							<div class="position-relative mb-4">
								<img class="img-fluid w-100" src="{{ asset('img/donation-1.jpg') }}" alt="">
								<a href="#" class="btn btn-sm btn-secondary px-3 position-absolute top-0 end-0">Food</a>
							</div>
							<a href="#" class="h3 d-inline-block">Healthy Food</a>
							<p>Through your donations and volunteer work, we spread kindness and support to children.</p>
							<a href="#" class="btn btn-primary w-100 py-3"><i class="fa fa-plus me-2"></i>Donate Now</a>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.13s">
					<div class="donation-item d-flex h-100 p-4">
						<div class="donation-progress d-flex flex-column flex-shrink-0 text-center me-4">
							<h6 class="mb-0">Raised</h6>
							<span class="mb-2">$8000</span>
							<div class="progress d-flex align-items-end w-100 h-100 mb-2">
								<div class="progress-bar w-100 bg-secondary" role="progressbar" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100">
									<span class="fs-4">95%</span>
								</div>
							</div>
							<h6 class="mb-0">Goal</h6>
							<span>$10000</span>
						</div>
						<div class="donation-detail">
							<div class="position-relative mb-4">
								<img class="img-fluid w-100" src="{{ asset('img/donation-2.jpg') }}" alt="">
								<a href="#" class="btn btn-sm btn-secondary px-3 position-absolute top-0 end-0">Health</a>
							</div>
							<a href="#" class="h3 d-inline-block">Water Treatment</a>
							<p>Through your donations and volunteer work, we spread kindness and support to children.</p>
							<a href="#" class="btn btn-primary w-100 py-3"><i class="fa fa-plus me-2"></i>Donate Now</a>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
					<div class="donation-item d-flex h-100 p-4">
						<div class="donation-progress d-flex flex-column flex-shrink-0 text-center me-4">
							<h6 class="mb-0">Raised</h6>
							<span class="mb-2">$8000</span>
							<div class="progress d-flex align-items-end w-100 h-100 mb-2">
								<div class="progress-bar w-100 bg-secondary" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
									<span class="fs-4">75%</span>
								</div>
							</div>
							<h6 class="mb-0">Goal</h6>
							<span>$10000</span>
						</div>
						<div class="donation-detail">
							<div class="position-relative mb-4">
								<img class="img-fluid w-100" src="{{ asset('img/donation-3.jpg') }}" alt="">
								<a href="#" class="btn btn-sm btn-secondary px-3 position-absolute top-0 end-0">Education</a>
							</div>
							<a href="#" class="h3 d-inline-block">Education Support</a>
							<p>Through your donations and volunteer work, we spread kindness and support to children.</p>
							<a href="#" class="btn btn-primary w-100 py-3"><i class="fa fa-plus me-2"></i>Donate Now</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div> -->
	
	<!-- Donation End -->

	<!-- Banner Start -->
	<div class="container-fluid banner py-5">
		<div class="container">
			<div class="banner-inner bg-light p-5 wow fadeIn" data-wow-delay="0.1s">
				<div class="row justify-content-center">
					<div class="col-lg-8 py-5 text-center">
						<h1 class="display-6 wow fadeIn" data-wow-delay="0.3s">
							Together, We Build the Future of ABU Zaria
						</h1>
						<p class="fs-5 mb-4 wow fadeIn" data-wow-delay="0.5s">
							Your donations and contributions to ABU Endowments and projects strengthen education, research, and community impact — empowering generations to come.
						</p>
						<div class="d-flex justify-content-center wow fadeIn" data-wow-delay="0.7s">
							<a class="btn btn-primary py-3 px-4 me-3" href="#">Donate Now</a>
							<a class="btn btn-secondary py-3 px-4" href="#">Be a Partner</a>
            </div>
        </div>
				</div>
			</div>
		</div>
	</div>

	<!-- Banner End -->

	<!-- Event Start -->
	<div class="container-fluid py-5">
		<div class="container">
			<div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 500px;">
				<p class="section-title bg-white text-center text-primary px-3">Events</p>
				<h1 class="display-6 mb-4">Be Part of ABU’s Global Giving Movement</h1>
			</div>
			<div class="row g-4">
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
					<div class="event-item h-100 p-4">
						<img class="img-fluid w-100 mb-4" src="{{ asset('img/Fund.png') }}" alt="">
						<a href="#" class="h3 d-inline-block">Alumni Fundraising Drive</a>
						<p>Your contributions empower ABU to expand scholarships and support bright students in need.</p>
						<div class="bg-light p-4">
							<p class="mb-1"><i class="fa fa-clock text-primary me-2"></i>09:00 AM - 04:00 PM</p>
							<p class="mb-1"><i class="fa fa-calendar-alt text-primary me-2"></i>Feb 15 - Feb 20</p>
							<p class="mb-0"><i class="fa fa-map-marker-alt text-primary me-2"></i>ABU Main Campus, Zaria</p>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.3s">
					<div class="event-item h-100 p-4">
						<img class="img-fluid w-100 mb-4" src="{{ asset('img/Research.png') }}" alt="">
						<a href="#" class="h3 d-inline-block">Research & Innovation Forum</a>
						<p>Donations support groundbreaking research projects that drive national and global development.</p>
						<div class="bg-light p-4">
							<p class="mb-1"><i class="fa fa-clock text-primary me-2"></i>10:00 AM - 05:00 PM</p>
							<p class="mb-1"><i class="fa fa-calendar-alt text-primary me-2"></i>Mar 05 - Mar 07</p>
							<p class="mb-0"><i class="fa fa-map-marker-alt text-primary me-2"></i>Assembly Hall, ABU Zaria</p>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
					<div class="event-item h-100 p-4">
						<img class="img-fluid w-100 mb-4" src="{{ asset('img/Community.png') }}" alt="">
						<a href="#" class="h3 d-inline-block">Community Development Outreach</a>
						<p>Through endowment support, ABU extends impact to surrounding communities in health and education.</p>
						<div class="bg-light p-4">
							<p class="mb-1"><i class="fa fa-clock text-primary me-2"></i>08:00 AM - 02:00 PM</p>
							<p class="mb-1"><i class="fa fa-calendar-alt text-primary me-2"></i>Apr 10 - Apr 12</p>
							<p class="mb-0"><i class="fa fa-map-marker-alt text-primary me-2"></i>Sabon Gari, Kaduna State</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Event End -->

	<!-- Donate Start -->
	<div id="donation" class="container-fluid donate py-5">
		<div class="container">
			<div class="row g-0">
				<!-- Text Section -->
				<div class="col-lg-7 donate-text bg-light py-5 wow fadeIn" data-wow-delay="0.1s">
					<div class="d-flex flex-column justify-content-center h-100 p-5 wow fadeIn" data-wow-delay="0.3s">
						<h1 class="display-6 mb-4">Support ABU Endowment & Projects</h1>
						<p class="fs-5 mb-0">
							Every donation strengthens Ahmadu Bello University Zaria — funding scholarships, research, 
							infrastructure, and community development. Together, we build a brighter future for generations to come.
						</p>
					</div>
				</div>
				<!-- Donation Form -->
				<div class="col-lg-5 donate-form bg-primary py-5 text-center wow fadeIn" data-wow-delay="0.5s">
					<div class="h-100 p-5">
						<!-- User Info Display (when logged in) -->
						<div id="userInfoDisplay" class="mb-4 text-white" style="display: none;">
							<div class="d-flex align-items-center justify-content-between mb-3">
								<div>
									<h6 class="mb-0">Logged in as:</h6>
									<small id="loggedInUserName"></small>
								</div>
								<button type="button" class="btn btn-sm btn-light" id="logoutBtn">Logout</button>
							</div>
						</div>
						
						<form id="donationForm">
							<div class="row g-3">
								<!-- Pre-filled fields when logged in (hidden) -->
								<input type="hidden" id="donorId" value="">
								<input type="hidden" id="donorSessionId" value="">
								
								<div class="col-12" id="donorSurnameField">
									<div class="form-floating">
										<input type="text" class="form-control" id="donorSurname" placeholder="Surname" required>
										<label for="donorSurname">Surname *</label>
									</div>
								</div>
								<div class="col-12" id="donorNameField">
									<div class="form-floating">
										<input type="text" class="form-control" id="donorName" placeholder="First Name" required>
										<label for="donorName">First Name *</label>
									</div>
								</div>
								<div class="col-12" id="donorOtherNameField">
									<div class="form-floating">
										<input type="text" class="form-control" id="donorOtherName" placeholder="Other Name">
										<label for="donorOtherName">Other Name (Optional)</label>
									</div>
								</div>
								<div class="col-12" id="donorEmailField">
									<div class="form-floating">
										<input type="email" class="form-control" id="donorEmail" placeholder="Your Email" required>
										<label for="donorEmail">Your Email *</label>
									</div>
								</div>
								<div class="col-12" id="donorPhoneField">
									<div class="form-floating">
										<input type="tel" class="form-control" id="donorPhone" placeholder="Phone Number" required>
										<label for="donorPhone">Phone Number *</label>
									</div>
								</div>
								<div class="col-12">
									<label class="form-label text-white mb-2">Select Amount</label>
									<div class="btn-group w-100" role="group" aria-label="Donation Amount Options">
										<input type="radio" class="btn-check" name="donationAmount" id="amount5000" value="5000" autocomplete="off" checked>
										<label class="btn btn-light" for="amount5000">₦5,000</label>

										<input type="radio" class="btn-check" name="donationAmount" id="amount10000" value="10000" autocomplete="off">
										<label class="btn btn-light" for="amount10000">₦10,000</label>

										<input type="radio" class="btn-check" name="donationAmount" id="amount20000" value="20000" autocomplete="off">
										<label class="btn btn-light" for="amount20000">₦20,000</label>

										<input type="radio" class="btn-check" name="donationAmount" id="amount50000" value="50000" autocomplete="off">
										<label class="btn btn-light" for="amount50000">₦50,000</label>

										<input type="radio" class="btn-check" name="donationAmount" id="amount100000" value="100000" autocomplete="off">
										<label class="btn btn-light" for="amount100000">₦100,000</label>
									</div>
								</div>
								<div class="col-12">
									<div class="form-floating">
										<input type="number" class="form-control" id="customAmount" placeholder="Or Enter Custom Amount" min="100">
										<label for="customAmount">Or Enter Custom Amount (Min: ₦100)</label>
									</div>
									<small class="text-white mt-1 d-block">Leave empty to use selected amount above</small>
								</div>
								<div class="col-12">
									<div class="alert alert-warning d-none" id="donationError" role="alert"></div>
									<div class="alert alert-info d-none" id="donationInfo" role="alert"></div>
								</div>
								<div class="col-12">
									<button class="btn btn-secondary py-3 w-100" type="submit" id="donateBtn">
										<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
										<span>Contribute Now</span>
									</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Donate End -->

	<!-- Registration Modal -->
	<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header bg-primary text-white">
					<h5 class="modal-title" id="registrationModalLabel">Create Account</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body p-4">
					<form id="registrationForm">
						<div class="row g-3">
							<div class="col-12">
								<label class="form-label">I am a: *</label>
								<select class="form-select" id="regDonorType" required>
									<option value="">Select your type</option>
									<option value="addressable_alumni">Alumni</option>
									<option value="supporter">Supporter</option>
									<option value="non_addressable_alumni">Other</option>
								</select>
								<small class="text-muted">Select Alumni if you graduated from ABU, Supporter if you're a friend/well-wisher, or Other</small>
							</div>
							
							<!-- Alumni-specific fields -->
							<div class="col-12" id="alumniFields" style="display: none;">
								<div class="row g-3">
									<div class="col-md-6">
										<label class="form-label">Registration Number *</label>
										<input type="text" class="form-control" id="regRegNumber" placeholder="e.g., U16/ENG/1234">
									</div>
									<div class="col-md-6">
										<label class="form-label">Entry Year</label>
										<input type="number" class="form-control" id="regEntryYear" min="1950" max="{{ date('Y') }}" placeholder="e.g., 2016">
									</div>
									<div class="col-md-6">
										<label class="form-label">Graduation Year</label>
										<input type="number" class="form-control" id="regGraduationYear" min="1950" max="{{ date('Y') }}" placeholder="e.g., 2020">
									</div>
									<div class="col-md-6">
										<label class="form-label">Faculty *</label>
										<select class="form-select" id="regFacultyId">
											<option value="">Select Faculty</option>
											@foreach(\App\Models\Faculty::all() as $faculty)
												<option value="{{ $faculty->id }}">{{ $faculty->current_name }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-6">
										<label class="form-label">Department *</label>
										<select class="form-select" id="regDepartmentId" disabled>
											<option value="">Select Department</option>
										</select>
									</div>
								</div>
							</div>

							<div class="col-md-6">
								<label class="form-label">Surname *</label>
								<input type="text" class="form-control" id="regSurname" required>
							</div>
							<div class="col-md-6">
								<label class="form-label">First Name *</label>
								<input type="text" class="form-control" id="regName" required>
							</div>
							<div class="col-12">
								<label class="form-label">Other Name (Optional)</label>
								<input type="text" class="form-control" id="regOtherName">
							</div>
							<div class="col-md-6">
								<label class="form-label">Email *</label>
								<input type="email" class="form-control" id="regEmail" required>
							</div>
							<div class="col-md-6">
								<label class="form-label">Phone Number *</label>
								<input type="tel" class="form-control" id="regPhone" required>
							</div>
							<div class="col-md-4">
								<label class="form-label">State *</label>
								<input type="text" class="form-control" id="regState" required>
							</div>
							<div class="col-md-4">
								<label class="form-label">LGA *</label>
								<input type="text" class="form-control" id="regLga" required>
							</div>
							<div class="col-md-4">
								<label class="form-label">Nationality *</label>
								<input type="text" class="form-control" id="regNationality" value="Nigerian" required>
							</div>
							<div class="col-12">
								<label class="form-label">Username *</label>
								<input type="text" class="form-control" id="regUsername" required minlength="3">
								<small class="text-muted">Choose a unique username for login</small>
							</div>
							<div class="col-md-6">
								<label class="form-label">Password *</label>
								<input type="password" class="form-control" id="regPassword" required minlength="6">
							</div>
							<div class="col-md-6">
								<label class="form-label">Confirm Password *</label>
								<input type="password" class="form-control" id="regPasswordConfirm" required>
							</div>
							<div class="col-12">
								<div class="alert alert-danger d-none" id="regError"></div>
								<div class="alert alert-success d-none" id="regSuccess"></div>
							</div>
							<div class="col-12">
								<button type="submit" class="btn btn-primary w-100" id="regSubmitBtn">
									<span class="spinner-border spinner-border-sm d-none" role="status"></span>
									<span>Register</span>
								</button>
								<p class="text-center mt-3 mb-0">
									Already have an account? <a href="#" class="text-primary" onclick="event.preventDefault(); $('#registrationModal').modal('hide'); $('#loginModal').modal('show');">Login here</a>
								</p>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- Login Modal -->
	<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header bg-primary text-white">
					<h5 class="modal-title" id="loginModalLabel">Login to Your Account</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body p-4">
					<form id="loginForm">
						<div class="mb-3">
							<label class="form-label">Username *</label>
							<input type="text" class="form-control" id="loginUsername" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Password *</label>
							<input type="password" class="form-control" id="loginPassword" required>
						</div>
						<div class="mb-3">
							<div class="alert alert-danger d-none" id="loginError"></div>
						</div>
						<button type="submit" class="btn btn-primary w-100" id="loginSubmitBtn">
							<span class="spinner-border spinner-border-sm d-none" role="status"></span>
							<span>Login</span>
						</button>
						<p class="text-center mt-3 mb-0">
							Don't have an account? <a href="#" class="text-primary" onclick="event.preventDefault(); $('#loginModal').modal('hide'); $('#registrationModal').modal('show');">Register here</a>
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- News & Events Start -->
	<div id="news-events" class="container-fluid py-5">
		<div class="container">
			<div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 500px;">
				<p class="section-title bg-white text-center text-primary px-3">News & Events</p>
				<h1 class="display-6 mb-4">Stay Connected with ABU Endowment</h1>
				<p class="fs-5 mb-5">Stay updated with the latest news, achievements, and upcoming events from ABU Endowment Fund.</p>
			</div>
			
			<!-- News Section -->
			<div class="row mb-5">
				<div class="col-12 mb-4">
					<h2 class="text-primary mb-4 wow fadeIn" data-wow-delay="0.2s"><i class="fa fa-newspaper me-2"></i>Latest News</h2>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
					<div class="news-item bg-light p-4 h-100">
						<div class="d-flex align-items-center mb-3">
							<div class="btn-square bg-primary text-white me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
								<i class="fa fa-calendar-check fa-2x"></i>
							</div>
							<div>
								<small class="text-primary">December 15, 2024</small>
								<h5 class="mb-0">Endowment Milestone Reached</h5>
							</div>
						</div>
						<p class="mb-3">ABU Endowment Fund reaches ₦500 million milestone, thanks to generous contributions from alumni and supporters worldwide. This achievement will fund 500+ scholarships for deserving students.</p>
						<a href="#" class="btn btn-sm btn-primary">Read More <i class="fa fa-arrow-right ms-1"></i></a>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.3s">
					<div class="news-item bg-light p-4 h-100">
						<div class="d-flex align-items-center mb-3">
							<div class="btn-square bg-primary text-white me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
								<i class="fa fa-graduation-cap fa-2x"></i>
							</div>
							<div>
								<small class="text-primary">November 28, 2024</small>
								<h5 class="mb-0">Scholarship Program Expansion</h5>
							</div>
						</div>
						<p class="mb-3">New scholarship program launched for postgraduate students in Engineering and Sciences. Applications open for the 2025/2026 academic session. Over 100 students to benefit.</p>
						<a href="#" class="btn btn-sm btn-primary">Read More <i class="fa fa-arrow-right ms-1"></i></a>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
					<div class="news-item bg-light p-4 h-100">
						<div class="d-flex align-items-center mb-3">
							<div class="btn-square bg-primary text-white me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
								<i class="fa fa-building fa-2x"></i>
							</div>
							<div>
								<small class="text-primary">October 10, 2024</small>
								<h5 class="mb-0">New Research Facility Inaugurated</h5>
							</div>
						</div>
						<p class="mb-3">State-of-the-art research laboratory funded by endowment donations opens at Faculty of Science. The facility will support cutting-edge research in biotechnology and renewable energy.</p>
						<a href="#" class="btn btn-sm btn-primary">Read More <i class="fa fa-arrow-right ms-1"></i></a>
					</div>
				</div>
			</div>

			<!-- Events Section -->
			<div class="row">
				<div class="col-12 mb-4">
					<h2 class="text-primary mb-4 wow fadeIn" data-wow-delay="0.2s"><i class="fa fa-calendar-alt me-2"></i>Upcoming Events</h2>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
					<div class="event-item h-100 p-4 bg-light">
						<div class="position-relative mb-4">
							<img class="img-fluid w-100 rounded" src="{{ asset('img/Fund.png') }}" alt="Alumni Fundraising Drive" style="height: 200px; object-fit: cover;">
							<div class="position-absolute top-0 start-0 m-3">
								<span class="badge bg-primary fs-6">Upcoming</span>
							</div>
						</div>
						<a href="#" class="h4 d-inline-block text-decoration-none mb-3">Annual Alumni Fundraising Gala</a>
						<p class="mb-3">Join us for our annual fundraising dinner bringing together ABU alumni, donors, and partners. This prestigious event celebrates achievements and raises funds for endowment projects.</p>
						<div class="bg-white p-3 rounded">
							<p class="mb-2"><i class="fa fa-clock text-primary me-2"></i><strong>Time:</strong> 6:00 PM - 11:00 PM</p>
							<p class="mb-2"><i class="fa fa-calendar-alt text-primary me-2"></i><strong>Date:</strong> February 15, 2025</p>
							<p class="mb-0"><i class="fa fa-map-marker-alt text-primary me-2"></i><strong>Venue:</strong> ABU Convocation Square, Zaria</p>
						</div>
						<a href="#" class="btn btn-primary w-100 mt-3"><i class="fa fa-calendar-check me-2"></i>Register Now</a>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.3s">
					<div class="event-item h-100 p-4 bg-light">
						<div class="position-relative mb-4">
							<img class="img-fluid w-100 rounded" src="{{ asset('img/Research.png') }}" alt="Research Forum" style="height: 200px; object-fit: cover;">
							<div class="position-absolute top-0 start-0 m-3">
								<span class="badge bg-primary fs-6">Upcoming</span>
							</div>
						</div>
						<a href="#" class="h4 d-inline-block text-decoration-none mb-3">Research & Innovation Summit</a>
						<p class="mb-3">A three-day summit showcasing research projects funded by ABU Endowment. Features presentations, exhibitions, and networking opportunities for researchers and donors.</p>
						<div class="bg-white p-3 rounded">
							<p class="mb-2"><i class="fa fa-clock text-primary me-2"></i><strong>Time:</strong> 9:00 AM - 5:00 PM Daily</p>
							<p class="mb-2"><i class="fa fa-calendar-alt text-primary me-2"></i><strong>Date:</strong> March 10-12, 2025</p>
							<p class="mb-0"><i class="fa fa-map-marker-alt text-primary me-2"></i><strong>Venue:</strong> ABU Main Auditorium, Zaria</p>
						</div>
						<a href="#" class="btn btn-primary w-100 mt-3"><i class="fa fa-calendar-check me-2"></i>Register Now</a>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
					<div class="event-item h-100 p-4 bg-light">
						<div class="position-relative mb-4">
							<img class="img-fluid w-100 rounded" src="{{ asset('img/Community.png') }}" alt="Community Outreach" style="height: 200px; object-fit: cover;">
							<div class="position-absolute top-0 start-0 m-3">
								<span class="badge bg-success fs-6">Ongoing</span>
							</div>
						</div>
						<a href="#" class="h4 d-inline-block text-decoration-none mb-3">Community Development Week</a>
						<p class="mb-3">Week-long community outreach program funded by endowment contributions. Includes health screenings, educational workshops, and infrastructure improvement projects in surrounding communities.</p>
						<div class="bg-white p-3 rounded">
							<p class="mb-2"><i class="fa fa-clock text-primary me-2"></i><strong>Time:</strong> 8:00 AM - 4:00 PM Daily</p>
							<p class="mb-2"><i class="fa fa-calendar-alt text-primary me-2"></i><strong>Date:</strong> January 20-26, 2025</p>
							<p class="mb-0"><i class="fa fa-map-marker-alt text-primary me-2"></i><strong>Venue:</strong> Multiple Locations, Kaduna State</p>
						</div>
						<a href="#" class="btn btn-success w-100 mt-3"><i class="fa fa-users me-2"></i>Join as Volunteer</a>
					</div>
				</div>
			</div>

			<!-- Archive Section -->
			<div class="row mt-5">
				<div class="col-12 text-center">
					<a href="#" class="btn btn-primary btn-lg px-5 py-3 wow fadeIn" data-wow-delay="0.3s">
						<i class="fa fa-archive me-2"></i>View All News & Events Archive
					</a>
				</div>
			</div>
		</div>
	</div>
	<!-- News & Events End -->

	<!-- Team Start -->
	<!-- <div class="container-fluid py-5">
		<div class="container">
			<div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 500px;">
				<p class="section-title bg-white text-center text-primary px-3">Our Team</p>
				<h1 class="display-6 mb-4">Meet Our Dedicated Team Members</h1>
			</div>
			<div class="row g-4">
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
					<div class="team-item d-flex h-100 p-4">
						<div class="team-detail pe-4">
							<img class="img-fluid mb-4" src="{{ asset('img/team-1.jpg') }}" alt="">
							<h3>Boris Johnson</h3>
							<span>Founder & CEO</span>
						</div>
						<div class="team-social bg-light d-flex flex-column justify-content-center flex-shrink-0 p-4">
							<a class="btn btn-square btn-primary my-2" href="#"><i class="fab fa-facebook-f"></i></a>
							<a class="btn btn-square btn-primary my-2" href="#"><i class="fab fa-x-twitter"></i></a>
							<a class="btn btn-square btn-primary my-2" href="#"><i class="fab fa-instagram"></i></a>
							<a class="btn btn-square btn-primary my-2" href="#"><i class="fab fa-youtube"></i></a>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.3s">
					<div class="team-item d-flex h-100 p-4">
						<div class="team-detail pe-4">
							<img class="img-fluid mb-4" src="{{ asset('img/team-2.jpg') }}" alt="">
							<h3>Donald Pakura</h3>
							<span>Project Manager</span>
						</div>
						<div class="team-social bg-light d-flex flex-column justify-content-center flex-shrink-0 p-4">
							<a class="btn btn-square btn-primary my-2" href="#"><i class="fab fa-facebook-f"></i></a>
							<a class="btn btn-square btn-primary my-2" href="#"><i class="fab fa-x-twitter"></i></a>
							<a class="btn btn-square btn-primary my-2" href="#"><i class="fab fa-instagram"></i></a>
							<a class="btn btn-square btn-primary my-2" href="#"><i class="fab fa-youtube"></i></a>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
					<div class="team-item d-flex h-100 p-4">
						<div class="team-detail pe-4">
							<img class="img-fluid mb-4" src="{{ asset('img/team-3.jpg') }}" alt="">
							<h3>Alexander Bell</h3>
							<span>Volunteer</span>
						</div>
						<div class="team-social bg-light d-flex flex-column justify-content-center flex-shrink-0 p-4">
							<a class="btn btn-square btn-primary my-2" href="#"><i class="fab fa-facebook-f"></i></a>
							<a class="btn btn-square btn-primary my-2" href="#"><i class="fab fa-x-twitter"></i></a>
							<a class="btn btn-square btn-primary my-2" href="#"><i class="fab fa-instagram"></i></a>
							<a class="btn btn-square btn-primary my-2" href="#"><i class="fab fa-youtube"></i></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div> -->
	<!-- Team End -->

	<!-- Testimonial Start -->
	<div class="container-fluid py-5">
		<div class="container">
			<div class="row g-5">
				<div class="col-md-12 col-lg-4 col-xl-3 wow fadeIn" data-wow-delay="0.1s">
					<div class="testimonial-title">
						<h1 class="display-6 mb-4">What Donors & Supporters Say About Our Projects.</h1>
						<p class="fs-5 mb-0">Your generosity helps build a stronger ABU, shaping brighter futures through education, research, and community impact.</p>
					</div>
				</div>
				<div class="col-md-12 col-lg-8 col-xl-9">
					<div class="owl-carousel testimonial-carousel wow fadeIn" data-wow-delay="0.3s">
						<div class="testimonial-item">
							<div class="row g-5 align-items-center">
								<div class="col-md-6">
									<div class="testimonial-img">
										<img class="img-fluid" src="{{ asset('img/testimonial-1.jpg') }}" alt="">
									</div>
								</div>
								<div class="col-md-6">
									<div class="testimonial-text pb-5 pb-md-0">
										<div class="mb-2">
											<i class="fa fa-star text-primary"></i>
											<i class="fa fa-star text-primary"></i>
											<i class="fa fa-star text-primary"></i>
											<i class="fa fa-star text-primary"></i>
											<i class="fa fa-star text-primary"></i>
										</div>
										<p class="fs-5">Supporting ABU means investing in the future. Donations to endowment and research programs help young minds achieve excellence and innovation.</p>
										<div class="d-flex align-items-center">
											<div class="btn-lg-square bg-light text-secondary flex-shrink-0">
												<i class="fa fa-quote-right fa-2x"></i>
											</div>
											<div class="ps-3">
												<h5 class="mb-0">Dr. Amina Yusuf</h5>
												<span>Alumni Donor</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="testimonial-item">
							<div class="row g-5 align-items-center">
								<div class="col-md-6">
									<div class="testimonial-img">
										<img class="img-fluid" src="{{ asset('img/testimonial-2.jpg') }}" alt="">
									</div>
								</div>
								<div class="col-md-6">
									<div class="testimonial-text pb-5 pb-md-0">
										<div class="mb-2">
											<i class="fa fa-star text-primary"></i>
											<i class="fa fa-star text-primary"></i>
											<i class="fa fa-star text-primary"></i>
											<i class="fa fa-star text-primary"></i>
											<i class="fa fa-star text-primary"></i>
										</div>
										<p class="fs-5">Being part of ABU’s crowdfunding initiatives allows me to give back directly to projects that transform lives and uplift communities.</p>
										<div class="d-flex align-items-center">
											<div class="btn-lg-square bg-light text-secondary flex-shrink-0">
												<i class="fa fa-quote-right fa-2x"></i>
											</div>
											<div class="ps-3">
												<h5 class="mb-0">Engr. John Okeke</h5>
												<span>Philanthropist</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="testimonial-item">
							<div class="row g-5 align-items-center">
								<div class="col-md-6">
									<div class="testimonial-img">
										<img class="img-fluid" src="{{ asset('img/testimonial-3.jpg') }}" alt="">
									</div>
								</div>
								<div class="col-md-6">
									<div class="testimonial-text pb-5 pb-md-0">
										<div class="mb-2">
											<i class="fa fa-star text-primary"></i>
											<i class="fa fa-star text-primary"></i>
											<i class="fa fa-star text-primary"></i>
											<i class="fa fa-star text-primary"></i>
											<i class="fa fa-star text-primary"></i>
										</div>
										<p class="fs-5">Contributing to ABU’s endowment fund is a chance to leave a lasting legacy and empower future generations of leaders.</p>
										<div class="d-flex align-items-center">
											<div class="btn-lg-square bg-light text-secondary flex-shrink-0">
												<i class="fa fa-quote-right fa-2x"></i>
											</div>
											<div class="ps-3">
												<h5 class="mb-0">Prof. Ibrahim Musa</h5>
												<span>ABU Lecturer & Supporter</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Testimonial End -->

	<!-- Newsletter Start -->
	<div class="container-fluid bg-primary py-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-lg-7 text-center wow fadeIn" data-wow-delay="0.5s">
					<h1 class="display-6 mb-4">Subscribe the Newsletter</h1>
					<div class="position-relative w-100 mb-2">
						<input class="form-control border-0 w-100 ps-4 pe-5" type="text" placeholder="Enter Your Email" style="height: 60px;">
						<button type="button" class="btn btn-lg-square shadow-none position-absolute top-0 end-0 mt-2 me-2"><i class="fa fa-paper-plane text-primary fs-4"></i></button>
					</div>
					<p class="mb-0">Don't worry, we won't spam you with emails.</p>
				</div>
			</div>
		</div>
	</div>
	<!-- Newsletter End -->

@push('styles')
<style>
/* Toast Notification Styles */
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    min-width: 300px;
    max-width: 500px;
    padding: 16px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 10000;
    opacity: 0;
    transform: translateX(400px);
    transition: all 0.3s ease-in-out;
    font-size: 14px;
    line-height: 1.5;
}

.toast-notification.show {
    opacity: 1;
    transform: translateX(0);
}

.toast-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    border-left: 4px solid #047857;
}

.toast-error {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    border-left: 4px solid #b91c1c;
}

.toast-info {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #ffffff;
    border-left: 4px solid #1d4ed8;
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.toast-content i {
    font-size: 20px;
    flex-shrink: 0;
}

.toast-message {
    flex: 1;
    font-weight: 500;
}

@media (max-width: 768px) {
    .toast-notification {
        top: 10px;
        right: 10px;
        left: 10px;
        min-width: auto;
        max-width: none;
        transform: translateY(-100px);
    }
    
    .toast-notification.show {
        transform: translateY(0);
    }
}

/* Navbar Authentication Styles */
#authSection {
    margin-right: 10px;
}

#loggedInUser .nav-link {
    color: #ffffff !important;
    padding: 0.5rem 1rem;
}

#loggedInUser .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

#loggedInUser .dropdown-menu {
    min-width: 200px;
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

#loggedInUser .dropdown-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

#notLoggedIn .btn {
    white-space: nowrap;
    font-size: 0.875rem;
}

@media (max-width: 991px) {
    #authSection {
        margin-top: 10px;
        margin-right: 0;
        width: 100%;
        justify-content: flex-end;
    }
    
    #notLoggedIn {
        width: 100%;
        justify-content: flex-end;
    }
    
    #notLoggedIn .btn {
        flex: 1;
        max-width: 120px;
    }
}
</style>
@endpush

@push('scripts')
<!-- Paystack Inline JS -->
<script src="https://js.paystack.co/v1/inline.js"></script>

<script>
// ============================================
// AUTHENTICATION & SESSION MANAGEMENT
// ============================================
const AuthManager = {
    getSession: function() {
        const session = localStorage.getItem('donor_session');
        return session ? JSON.parse(session) : null;
    },
    
    setSession: function(sessionData) {
        localStorage.setItem('donor_session', JSON.stringify(sessionData));
        this.updateUI();
    },
    
    clearSession: function() {
        localStorage.removeItem('donor_session');
        this.updateUI();
    },
    
    isAuthenticated: function() {
        return this.getSession() !== null;
    },
    
    updateUI: function() {
        const session = this.getSession();
        const userInfoDisplay = document.getElementById('userInfoDisplay');
        const loggedInUserName = document.getElementById('loggedInUserName');
        
        // Update Navbar
        this.updateNavbar(session);
        
        if (session && userInfoDisplay) {
            userInfoDisplay.style.display = 'block';
            if (loggedInUserName) {
                loggedInUserName.textContent = session.username + ' (' + (session.donor?.full_name || session.donor?.name || 'User') + ')';
            }
            
            // Hide form fields and pre-fill
            const fields = ['donorSurnameField', 'donorNameField', 'donorOtherNameField', 'donorEmailField', 'donorPhoneField'];
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) field.style.display = 'none';
            });
            
            // Pre-fill hidden inputs
            if (session.donor) {
                document.getElementById('donorId').value = session.donor.id;
                document.getElementById('donorSurname').value = session.donor.surname || '';
                document.getElementById('donorName').value = session.donor.name || '';
                document.getElementById('donorOtherName').value = session.donor.other_name || '';
                document.getElementById('donorEmail').value = session.donor.email || '';
                document.getElementById('donorPhone').value = session.donor.phone || '';
            }
            if (session.id) {
                document.getElementById('donorSessionId').value = session.id;
            }
        } else {
            if (userInfoDisplay) userInfoDisplay.style.display = 'none';
            
            // Show form fields
            const fields = ['donorSurnameField', 'donorNameField', 'donorOtherNameField', 'donorEmailField', 'donorPhoneField'];
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) field.style.display = 'block';
            });
        }
    },
    
    updateNavbar: function(session) {
        const loggedInUser = document.getElementById('loggedInUser');
        const notLoggedIn = document.getElementById('notLoggedIn');
        const userDisplayName = document.getElementById('userDisplayName');
        const dropdownUserName = document.getElementById('dropdownUserName');
        
        if (session) {
            // User is logged in
            if (loggedInUser) loggedInUser.classList.remove('d-none');
            if (notLoggedIn) notLoggedIn.classList.add('d-none');
            
            // Update user display name
            const displayName = session.donor?.full_name || 
                               (session.donor ? `${session.donor.surname || ''} ${session.donor.name || ''}`.trim() : session.username) || 
                               session.username;
            
            if (userDisplayName) {
                userDisplayName.textContent = displayName.length > 15 ? displayName.substring(0, 15) + '...' : displayName;
            }
            if (dropdownUserName) {
                dropdownUserName.textContent = displayName;
            }
        } else {
            // User is not logged in
            if (loggedInUser) loggedInUser.classList.add('d-none');
            if (notLoggedIn) notLoggedIn.classList.remove('d-none');
        }
    }
};

// ============================================
// TOAST NOTIFICATION SYSTEM
// ============================================
const ToastManager = {
    show: function(message, type = 'success', duration = 5000) {
        // Remove existing toast if any
        const existingToast = document.getElementById('toastNotification');
        if (existingToast) {
            existingToast.remove();
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.id = 'toastNotification';
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span class="toast-message">${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Auto remove after duration
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, duration);
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // Check for payment status in URL
    const urlParams = new URLSearchParams(window.location.search);
    const paymentStatus = urlParams.get('payment_status');
    const reference = urlParams.get('reference');
    const donorName = urlParams.get('donor_name');
    const amount = urlParams.get('amount');
    
    if (paymentStatus === 'success' && reference) {
        // Show toast notification with user's name
        const thankYouMessage = donorName 
            ? `🎉 Thank you, ${decodeURIComponent(donorName)}! Your donation of ₦${parseFloat(amount || 0).toLocaleString()} was successful.`
            : `🎉 Payment successful! Thank you for your donation of ₦${parseFloat(amount || 0).toLocaleString()}.`;
        
        ToastManager.show(thankYouMessage, 'success', 5000);
        
        // Also update donation info if element exists
        const donationInfo = document.getElementById('donationInfo');
        if (donationInfo) {
            donationInfo.classList.remove('d-none');
            donationInfo.classList.remove('alert-warning');
            donationInfo.classList.add('alert-success');
            donationInfo.textContent = 'Payment successful! A confirmation email has been sent to your email address.';
        }
        
        // Scroll to form
        const donationForm = document.getElementById('donationForm');
        if (donationForm) {
            donationForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        // Clear URL parameters
        window.history.replaceState({}, document.title, window.location.pathname);
    } else if (paymentStatus === 'failed') {
        ToastManager.show('Payment failed. Please try again or contact support.', 'error', 5000);
        
        const donationError = document.getElementById('donationError');
        if (donationError) {
            donationError.classList.remove('d-none');
            donationError.textContent = 'Payment failed. Please try again or contact support.';
        }
        
        // Scroll to form
        const donationForm = document.getElementById('donationForm');
        if (donationForm) {
            donationForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        // Clear URL parameters
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    const donationForm = document.getElementById('donationForm');
    const donateBtn = document.getElementById('donateBtn');
    const donationError = document.getElementById('donationError');
    const donationInfo = document.getElementById('donationInfo');
    
    // Paystack public key from config
    const paystackPublicKey = '{{ config("services.paystack.public_key") }}';
    
    if (!paystackPublicKey) {
        console.error('Paystack public key is not configured');
        showError('Payment gateway is not configured. Please contact support.');
    }

    // Generate device fingerprint
    function generateDeviceFingerprint() {
        let fingerprint = localStorage.getItem('device_fingerprint');
        if (!fingerprint) {
            fingerprint = 'device_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('device_fingerprint', fingerprint);
        }
        return fingerprint;
    }

    // Get selected or custom amount
    function getDonationAmount() {
        const customAmount = parseFloat(document.getElementById('customAmount').value);
        if (customAmount && customAmount >= 100) {
            return customAmount;
        }
        const selectedAmount = document.querySelector('input[name="donationAmount"]:checked');
        return selectedAmount ? parseFloat(selectedAmount.value) : 5000;
    }

    // Show error message
    function showError(message) {
        donationError.textContent = message;
        donationError.classList.remove('d-none');
        donationInfo.classList.add('d-none');
    }

    // Show info message
    function showInfo(message) {
        donationInfo.textContent = message;
        donationInfo.classList.remove('d-none');
        donationError.classList.add('d-none');
    }

    // Hide messages
    function hideMessages() {
        donationError.classList.add('d-none');
        donationInfo.classList.add('d-none');
    }

    // Set loading state
    function setLoading(loading) {
        const spinner = donateBtn.querySelector('.spinner-border');
        const text = donateBtn.querySelector('span:not(.spinner-border)');
        if (loading) {
            spinner.classList.remove('d-none');
            donateBtn.disabled = true;
        } else {
            spinner.classList.add('d-none');
            donateBtn.disabled = false;
        }
    }

    // ============================================
    // REGISTRATION HANDLER
    // ============================================
    const registrationForm = document.getElementById('registrationForm');
    const regSubmitBtn = document.getElementById('regSubmitBtn');
    const regError = document.getElementById('regError');
    const regSuccess = document.getElementById('regSuccess');
    const regDonorType = document.getElementById('regDonorType');
    const alumniFields = document.getElementById('alumniFields');
    const regFacultyId = document.getElementById('regFacultyId');
    const regDepartmentId = document.getElementById('regDepartmentId');

    // Show/hide alumni fields based on donor type
    if (regDonorType) {
        regDonorType.addEventListener('change', function() {
            const isAlumni = ['addressable_alumni', 'non_addressable_alumni'].includes(this.value);
            if (alumniFields) {
                alumniFields.style.display = isAlumni ? 'block' : 'none';
                if (isAlumni) {
                    document.getElementById('regRegNumber').required = true;
                    document.getElementById('regFacultyId').required = true;
                    document.getElementById('regDepartmentId').required = true;
                } else {
                    document.getElementById('regRegNumber').required = false;
                    document.getElementById('regFacultyId').required = false;
                    document.getElementById('regDepartmentId').required = false;
                }
            }
        });
    }

    // Load departments when faculty is selected
    if (regFacultyId) {
        regFacultyId.addEventListener('change', async function() {
            const facultyId = this.value;
            if (!facultyId) {
                regDepartmentId.disabled = true;
                regDepartmentId.innerHTML = '<option value="">Select Department</option>';
                return;
            }

            try {
                const response = await fetch(`/api/faculties/${facultyId}/departments`);
                const data = await response.json();
                
                regDepartmentId.innerHTML = '<option value="">Select Department</option>';
                if (data.success && data.data) {
                    data.data.forEach(dept => {
                        const option = document.createElement('option');
                        option.value = dept.id;
                        option.textContent = dept.current_name;
                        regDepartmentId.appendChild(option);
                    });
                }
                regDepartmentId.disabled = false;
            } catch (error) {
                console.error('Error loading departments:', error);
                regDepartmentId.innerHTML = '<option value="">Error loading departments</option>';
            }
        });
    }

    // Registration form submission
    if (registrationForm) {
        registrationForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            regError.classList.add('d-none');
            regSuccess.classList.add('d-none');
            
            const spinner = regSubmitBtn.querySelector('.spinner-border');
            const text = regSubmitBtn.querySelector('span:not(.spinner-border)');
            spinner.classList.remove('d-none');
            regSubmitBtn.disabled = true;

            // Get form values
            const donorType = regDonorType.value;
            const password = document.getElementById('regPassword').value;
            const passwordConfirm = document.getElementById('regPasswordConfirm').value;

            // Validate passwords match
            if (password !== passwordConfirm) {
                regError.textContent = 'Passwords do not match!';
                regError.classList.remove('d-none');
                spinner.classList.add('d-none');
                regSubmitBtn.disabled = false;
                return;
            }

            // Prepare donor data
            const donorData = {
                surname: document.getElementById('regSurname').value.trim(),
                name: document.getElementById('regName').value.trim(),
                other_name: document.getElementById('regOtherName').value.trim() || null,
                email: document.getElementById('regEmail').value.trim(),
                phone: document.getElementById('regPhone').value.trim(),
                state: document.getElementById('regState').value.trim(),
                lga: document.getElementById('regLga').value.trim(),
                nationality: document.getElementById('regNationality').value.trim(),
                donor_type: donorType
            };

            // Add alumni-specific fields
            if (['addressable_alumni', 'non_addressable_alumni'].includes(donorType)) {
                donorData.reg_number = document.getElementById('regRegNumber').value.trim();
                donorData.entry_year = document.getElementById('regEntryYear').value ? parseInt(document.getElementById('regEntryYear').value) : null;
                donorData.graduation_year = document.getElementById('regGraduationYear').value ? parseInt(document.getElementById('regGraduationYear').value) : null;
                donorData.faculty_id = regFacultyId.value ? parseInt(regFacultyId.value) : null;
                donorData.department_id = regDepartmentId.value ? parseInt(regDepartmentId.value) : null;
            }

            try {
                // Step 1: Create donor
                const donorResponse = await fetch('/api/donors', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(donorData)
                });

                const donorResult = await donorResponse.json();

                if (!donorResponse.ok) {
                    throw new Error(donorResult.message || donorResult.errors ? JSON.stringify(donorResult.errors) : 'Registration failed');
                }

                // Step 2: Create donor session
                const sessionResponse = await fetch('/api/donor-sessions/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        username: document.getElementById('regUsername').value.trim(),
                        password: password,
                        donor_id: donorResult.donor.id
                    })
                });

                const sessionResult = await sessionResponse.json();

                if (!sessionResponse.ok) {
                    throw new Error(sessionResult.message || 'Failed to create account');
                }

                // Step 3: Login automatically
                const loginResponse = await fetch('/api/donor-sessions/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        username: document.getElementById('regUsername').value.trim(),
                        password: password
                    })
                });

                const loginResult = await loginResponse.json();

                if (!loginResponse.ok) {
                    throw new Error(loginResult.message || 'Registration successful but login failed. Please login manually.');
                }

                // Normalize session data (API returns session_id, we need id)
                const sessionData = {
                    id: loginResult.data.session_id,
                    username: loginResult.data.username,
                    donor: loginResult.data.donor,
                    device_session_id: loginResult.data.device_session_id
                };
                
                // Save session
                AuthManager.setSession(sessionData);
                
                // Show success and close modal
                regSuccess.textContent = 'Registration successful! You are now logged in.';
                regSuccess.classList.remove('d-none');
                
                setTimeout(() => {
                    $('#registrationModal').modal('hide');
                    registrationForm.reset();
                    regError.classList.add('d-none');
                    regSuccess.classList.add('d-none');
                }, 1500);

            } catch (error) {
                console.error('Registration error:', error);
                regError.textContent = error.message || 'Registration failed. Please try again.';
                regError.classList.remove('d-none');
            } finally {
                spinner.classList.add('d-none');
                regSubmitBtn.disabled = false;
            }
        });
    }

    // ============================================
    // LOGIN HANDLER
    // ============================================
    const loginForm = document.getElementById('loginForm');
    const loginSubmitBtn = document.getElementById('loginSubmitBtn');
    const loginError = document.getElementById('loginError');

    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            loginError.classList.add('d-none');
            
            const spinner = loginSubmitBtn.querySelector('.spinner-border');
            const text = loginSubmitBtn.querySelector('span:not(.spinner-border)');
            spinner.classList.remove('d-none');
            loginSubmitBtn.disabled = true;

            try {
                const response = await fetch('/api/donor-sessions/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        username: document.getElementById('loginUsername').value.trim(),
                        password: document.getElementById('loginPassword').value.trim()
                    })
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Login failed');
                }

                // Normalize session data (API returns session_id, we need id)
                const sessionData = {
                    id: result.data.session_id,
                    username: result.data.username,
                    donor: result.data.donor,
                    device_session_id: result.data.device_session_id
                };
                
                // Save session
                AuthManager.setSession(sessionData);
                
                // Close modal
                $('#loginModal').modal('hide');
                loginForm.reset();
                loginError.classList.add('d-none');

            } catch (error) {
                console.error('Login error:', error);
                loginError.textContent = error.message || 'Invalid username or password.';
                loginError.classList.remove('d-none');
            } finally {
                spinner.classList.add('d-none');
                loginSubmitBtn.disabled = false;
            }
        });
    }

    // ============================================
    // LOGOUT HANDLER
    // ============================================
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to logout?')) {
                AuthManager.clearSession();
                // Clear form
                const donationForm = document.getElementById('donationForm');
                if (donationForm) donationForm.reset();
            }
        });
    }
    
    // Navbar logout button handler
    const navbarLogoutBtn = document.getElementById('navbarLogoutBtn');
    if (navbarLogoutBtn) {
        navbarLogoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                AuthManager.clearSession();
                // Clear donation form if it exists
                const donationForm = document.getElementById('donationForm');
                if (donationForm) donationForm.reset();
                // Close any open dropdowns
                const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('userDropdown'));
                if (dropdown) dropdown.hide();
                // Show success message
                ToastManager.show('You have been logged out successfully.', 'info', 3000);
            }
        });
    }

    // ============================================
    // DONATION FORM HANDLER (UPDATED)
    // ============================================
    // Form submission handler
    donationForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Check if user is authenticated
        if (!AuthManager.isAuthenticated()) {
            // Show registration modal
            $('#registrationModal').modal('show');
            showError('Please register or login to make a donation.');
            return;
        }
        
        hideMessages();
        setLoading(true);

        // Get form values (use session data if available)
        const session = AuthManager.getSession();
        const surname = session?.donor?.surname || document.getElementById('donorSurname').value.trim();
        const name = session?.donor?.name || document.getElementById('donorName').value.trim();
        const otherName = session?.donor?.other_name || document.getElementById('donorOtherName').value.trim();
        const email = session?.donor?.email || document.getElementById('donorEmail').value.trim();
        const phone = session?.donor?.phone || document.getElementById('donorPhone').value.trim();
        const amount = getDonationAmount();

        // Validation
        if (!surname || !name || !email || !phone) {
            showError('Please fill in all required fields.');
            setLoading(false);
            return;
        }

        if (amount < 100) {
            showError('Minimum donation amount is ₦100.');
            setLoading(false);
            return;
        }

        try {
            // Get device fingerprint
            const deviceFingerprint = generateDeviceFingerprint();
            
            // Get callback URL
            const callbackUrl = window.location.origin + '/?payment_status=success';

            // Get authenticated user's donor_id if available
            const authenticatedDonorId = session?.donor?.id || null;

            // Initialize payment
            const response = await fetch('/api/payments/initialize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    email: email,
                    amount: amount,
                    callback_url: callbackUrl,
                    device_fingerprint: deviceFingerprint,
                    metadata: {
                        name: name,
                        surname: surname,
                        other_name: otherName || null,
                        phone: phone,
                        endowment: 'yes', // Default to endowment donation
                        type: 'endowment',
                        donor_id: authenticatedDonorId, // Include authenticated donor ID
                        donor_session_id: session?.id || null // Include session ID
                    }
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Payment initialization failed');
            }

            if (data.success && data.data && data.data.authorization_url) {
                if (!paystackPublicKey) {
                    throw new Error('Paystack public key is not configured');
                }
                
                // Open Paystack payment modal
                const handler = PaystackPop.setup({
                    key: paystackPublicKey,
                    email: email,
                    amount: amount * 100, // Convert to kobo
                    ref: data.data.reference,
                    metadata: {
                        donation_id: data.data.donation_id,
                        donor_id: data.data.donor.id,
                        custom_fields: [
                            {
                                display_name: "Full Name",
                                variable_name: "full_name",
                                value: `${surname} ${name} ${otherName}`.trim()
                            },
                            {
                                display_name: "Phone",
                                variable_name: "phone",
                                value: phone
                            }
                        ]
                    },
                    callback: function(response) {
                        // Payment successful - verify payment
                        window.location.href = `/api/payments/verify/${response.reference}?redirect=${encodeURIComponent(window.location.origin + '/?payment_status=success')}`;
                    },
                    onClose: function() {
                        showInfo('Payment window closed. You can try again when ready.');
                        setLoading(false);
                    }
                });
                
                handler.openIframe();
            } else {
                throw new Error('Failed to initialize payment. Please try again.');
            }

        } catch (error) {
            console.error('Payment error:', error);
            showError(error.message || 'An error occurred while processing your donation. Please try again.');
            setLoading(false);
        }
    });

    // Reset custom amount when radio button is selected
    document.querySelectorAll('input[name="donationAmount"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('customAmount').value = '';
        });
    });

    // Disable radio buttons when custom amount is entered
    document.getElementById('customAmount').addEventListener('input', function(e) {
        if (e.target.value && parseFloat(e.target.value) >= 100) {
            document.querySelectorAll('input[name="donationAmount"]').forEach(radio => {
                radio.checked = false;
            });
        }
    });

    // ============================================
    // INITIALIZE AUTHENTICATION ON PAGE LOAD
    // ============================================
    AuthManager.updateUI();
});
</script>
@endpush

@endsection