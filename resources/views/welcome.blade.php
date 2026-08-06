<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>CvSU Naic Campus - Laboratory Science High School</title>

  <meta name="description" content="CvSU Naic Campus - Laboratory Science High School, a public academic community committed to excellence in instruction, research, and student development." />
  <meta name="keywords" content="CvSU Naic, Cavite State University, Naic Campus, LSHS, Laboratory Science High School, education Cavite" />
  <meta name="author" content="CvSU Naic" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

  <style>
    @font-face {
      font-family: 'Aptos';
      src: url('{{ asset('fonts/Aptos.ttf') }}') format('truetype');
      font-weight: 400;
    }

    @font-face {
      font-family: 'Aptos';
      src: url('{{ asset('fonts/Aptos-Bold.ttf') }}') format('truetype');
      font-weight: 700;
    }

    :root {
      --cvsu-green: #0a4d00;
      --cvsu-gold: #e8c547;
      --muted: #6b7280;
      --surface: #ffffff;
      --surface-soft: #f3f8ef;
      --card-radius: 14px;
    }

    body {
      font-family: Aptos, "Segoe UI", system-ui, sans-serif;
      background: #f6f7f9;
      color: #0b1220;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    .navbar {
      background: linear-gradient(90deg, #0a4d00, #158304) !important;
      box-shadow: 0 4px 18px rgba(75, 213, 60, 0.08);
    }

    .nav-link {
      transition: color .2s ease, transform .2s ease;
    }

    .nav-link:hover {
      transform: translateY(-2px);
    }

    .dropdown-menu {
      border-radius: 10px;
      min-width: 220px;
    }

    .dropdown-item:hover {
      background: rgba(10, 77, 0, 0.06);
    }

    .hero {
      position: relative;
      padding: 72px 0;
      color: #fff;
      overflow: hidden;
      background: url('{{ asset("images/IMG_1164.jpg") }}') center/cover no-repeat;
    }

    .hero::before {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(2, 20, 5, 0.75), rgba(8, 77, 20, 0.48));
      z-index: 1;
    }

    .hero .container {
      position: relative;
      z-index: 2;
    }

    .hero .left h1,
    .hero .left p {
      text-shadow: 0 2px 8px rgba(0, 0, 0, 0.55);
      color: #f2f2f2;
    }

    .hero .right {
      padding-left: 30px;
    }

    .quick-action {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.12);
      color: #fff;
      text-decoration: none;
      border: 1px solid rgba(255, 255, 255, 0.14);
      transition: transform .2s ease, background-color .2s ease;
    }

    .quick-action:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
    }

    .card {
      border: none;
      border-radius: var(--card-radius);
      transition: all .3s ease;
      background: var(--surface);
      overflow: hidden;
    }

    .card:hover {
      transform: translateY(-6px) scale(1.01);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }

    .card-title {
      font-weight: 700;
      margin-bottom: .5rem;
    }

    .card-img-top {
      height: 220px;
      object-fit: cover;
    }

    .section-title {
      color: var(--cvsu-green);
      font-weight: 800;
      font-size: 1.8rem;
      letter-spacing: .5px;
      position: relative;
      padding-bottom: 6px;
    }

    .section-title::after {
      content: "";
      width: 60px;
      height: 4px;
      border-radius: 6px;
      background: var(--cvsu-gold);
      position: absolute;
      bottom: 0;
      left: 0;
    }

    .rounded-glass {
      background: #fff;
      border-radius: 12px;
      padding: 18px;
      box-shadow: 0 6px 30px rgba(2, 10, 3, 0.04);
    }

    .soft-panel {
      background: var(--surface-soft);
    }

    footer.site-footer {
      background: linear-gradient(90deg, #073800, var(--cvsu-green));
      color: #fff;
      padding: 32px 0;
    }

    footer a {
      color: var(--cvsu-gold);
      text-decoration: none;
    }

    footer.sub-footer {
      background: #061f00;
      color: #ccc;
      font-size: .875rem;
    }

    .calendar {
      background: #fff;
      color: #111;
      border-radius: 10px;
      padding: 12px;
      width: 100%;
    }

    .calendar .month {
      font-weight: 700;
      color: var(--cvsu-green);
    }

    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 6px;
      margin-top: 8px;
    }

    .calendar-grid div {
      padding: 8px;
      text-align: center;
      border-radius: 6px;
    }

    .calendar-grid .day {
      background: #f3f4f6;
      color: #111;
    }

    .calendar-grid .today {
      background: var(--cvsu-gold);
      color: #111;
      font-weight: 700;
    }

    .accordion-body,
    .accordion-body p,
    .accordion-body li {
      text-align: justify;
      line-height: 1.65;
    }

    .accordion-button {
      font-weight: 600;
      padding: 1rem 1.25rem;
      transition: all 0.3s ease;
    }

    .accordion-button:not(.collapsed) {
      background: var(--cvsu-green) !important;
      color: #fff !important;
      box-shadow: none;
    }

    .accordion-item {
      border: none;
      margin-bottom: 12px;
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }

    .accordion-button:focus {
      box-shadow: none;
    }

    .dropcap:first-letter {
      float: left;
      font-size: 3.2rem;
      font-weight: 700;
      line-height: 0;
      padding-right: 3px;
      padding-top: 4px;
      color: var(--cvsu-green);
    }

    @media (max-width: 991px) {
      .hero .left,
      .hero .right {
        padding: 0;
      }

      .hero {
        padding: 40px 0;
      }

      .hero .left h1 {
        font-size: 2rem;
      }

      .hero .right {
        padding-left: 0;
        margin-top: 20px;
      }
    }

    @media (max-width: 767px) {
      .hero .left h1 {
        font-size: 1.8rem;
      }

      .card {
        margin-bottom: 20px;
      }

      .calendar-grid {
        font-size: 0.8rem;
      }

      .hero .left .btn {
        width: 100%;
        margin-bottom: 10px;
      }

      .hero .left .btn + .btn {
        margin-left: 0 !important;
      }
    }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark sticky-top py-2">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#home">
        <img src="{{ asset('images/logo.jpg') }}" alt="CvSU Naic logo" style="height:55px; padding:2px; border-radius:6px;" />
        <div class="ms-2">
          <div class="fw-bold" style="font-size:14px;">CvSU Naic Campus</div>
          <div style="font-size:11px; opacity:.8;">Laboratory Science High School</div>
        </div>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item">
            <a class="nav-link active" href="#home">Home</a>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">About</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#history">History</a></li>
              <li><a class="dropdown-item" href="#vision">Vision & Mission</a></li>
              <li><a class="dropdown-item" href="#quality">Quality Policy</a></li>
              <li><a class="dropdown-item" href="#goals">Goals</a></li>
            </ul>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="#events">Events</a>
          </li>

          <li class="nav-item ms-lg-3">
            <a href="{{ route('login.student') }}" class="btn btn-warning btn-sm fw-bold px-3">
              Portal Login
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <section id="home" class="hero">
    <div class="container">
      <div class="row align-items-center flex-column-reverse flex-lg-row">
        <div class="col-lg-6 left hero-content">
          <h1 class="display-5 fw-bold">Welcome to Cavite State University - Naic Laboratory Science High School</h1>
          <p class="lead text-white">Empowering education, inspiring innovation, and building morally upright leaders through science and research.</p>
          <div class="mt-4">
            <a href="{{ route('enroll.form') }}" class="btn" style="background:var(--cvsu-gold); color:#111; font-weight:700; padding:10px 16px; border-radius:10px;">
              <i class="fa-solid fa-user-graduate me-2"></i> Enroll Now
            </a>
            <a href="#events" class="btn btn-outline-light ms-2" style="border-color: rgba(255,255,255,0.18); color:#fff;">
              <i class="fa-solid fa-calendar-days me-2"></i> Latest Events
            </a>
          </div>
          <div class="d-flex flex-wrap gap-3 mt-4">
            <a href="{{ route('login.student') }}" class="quick-action">
              <i class="fa-solid fa-user"></i> Student Login
            </a>
            <a href="{{ route('login.professor') }}" class="quick-action">
              <i class="fa-solid fa-chalkboard-user"></i> Teacher Login
            </a>
            {{-- <a href="{{ route('login.admin') }}" class="quick-action">
              <i class="fa-solid fa-user-shield"></i> Admin Login
            </a> --}}
          </div>
        </div>

        <div class="col-lg-6 right">
          <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner rounded" style="overflow:hidden;">
              <div class="carousel-item active">
                <img src="{{ asset('images/IMG_1174.jpg') }}" class="d-block w-100" alt="CvSU Naic students during a campus activity" style="height:320px; object-fit:cover;" />
              </div>
              <div class="carousel-item">
                <img src="{{ asset('images/img.png') }}" class="d-block w-100" alt="Laboratory Science High School event highlights" style="height:320px; object-fit:cover;" />
              </div>
              <div class="carousel-item">
                <img src="{{ asset('images/IMG_1151.jpg') }}" class="d-block w-100" alt="CvSU Naic campus community gathering" style="height:320px; object-fit:cover;" />
              </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <main class="container my-5">
    <section id="events" class="mb-5">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h3 class="section-title mb-0">High School Highlights</h3>
        <a href="#about" class="small-link" style="color:var(--muted)">Learn more about the school &raquo;</a>
      </div>
      <div class="row g-4">
        <div class="col-md-4 col-12">
          <article class="card">
            <img src="{{ asset('images/lab.jpg') }}" class="card-img-top" alt="Students presenting projects at the annual science fair" />
            <div class="card-body">
              <h5 class="card-title">Annual Science Fair</h5>
              <p class="text-muted">Student projects across biology, chemistry, engineering, and research showcased in a campus-wide celebration of inquiry.</p>
              <a href="#goals" class="btn btn-outline-success">See Our Goals</a>
            </div>
          </article>
        </div>
        <div class="col-md-4 col-12">
          <article class="card">
            <img src="{{ asset('images/present.jpg') }}" class="card-img-top" alt="Faculty workshop and teacher development session" />
            <div class="card-body">
              <h5 class="card-title">Teacher Training Workshop</h5>
              <p class="text-muted">Professional development for faculty in STEM teaching methods and lab safety.</p>
              <a href="#vision" class="btn btn-outline-success">View Mission</a>
            </div>
          </article>
        </div>
        <div class="col-md-4 col-12">
          <article class="card">
            <img src="{{ asset('images/library.jpg') }}" class="card-img-top" alt="Library and student collaboration area" />
            <div class="card-body">
              <h5 class="card-title">Library & Research Hub</h5>
              <p class="text-muted">State-of-the-art library and collaborative research spaces now open to students.</p>
              <a href="#history" class="btn btn-outline-success">Read History</a>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section id="about" class="mb-5" data-aos="fade-up">
      <h3 class="section-title mb-4">About Us</h3>
      <div class="accordion" id="aboutAccordion">
        <div class="accordion-item" data-aos="fade-up" id="history">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#historyCollapse" aria-expanded="true" aria-controls="historyCollapse">
              History
            </button>
          </h2>
          <div id="historyCollapse" class="accordion-collapse collapse show" data-bs-parent="#aboutAccordion">
            <div class="accordion-body rounded-glass soft-panel" style="padding: 1.5rem;">
              <p class="dropcap">
                <strong>CVSU NAIC HISTORY</strong> - Cavite School of Fisheries (CSF) was created under Republic Act No. 2661, sponsored by Cong. Justiniano Montano, the congressman for the lone district of Cavite, with initial appropriation of P 200,000.00, which was approved by Congress on June 18, 1960. Its implementation in 1961 seems to have a very slim chance due to financial constraints of the National Government. It was due to the efforts of Dr. Pedro G. Guiang, Asst. Director of Public Schools, who made representation to the budget commission to revert some reserved funds of other fishery schools that an initial amount of Php 40,000.00 was made available for CSF.
              </p>

              <p>
                Plans and budgetary proposals were prepared in July 1961. The item of one principal, three Fishery Technologists, and one clerk were created. The amount of Php 22,000.00 was set aside for the construction of the school building and the balance for supplies, materials, and equipment. Initial operation of the school began on June 4, 1962, with an enrollment of 175 first year students under the Secondary Fishery Education Curriculum and with an additional appropriation of Php 100,000.00. Classes were initially conducted in the old municipal building located in the heart of the town of Naic. After a year, the school was transferred to its present site in Bucana Malaki and Bagong Kalsada. CSF was converted to college in 1970, offering a two-and-a-half-year Technical Education Curriculum with 50 students enrolled.
              </p>

              <p>
                Since then, there was a continuous marked increase in enrollment, number of teaching and facilitative personnel, equipment, and other instructional facilities in both the Secondary and Technical Fishery Education Curricula. At the start of school year 1974-1975, the three-year Fishery Education Curriculum leading to a diploma in Fishery Technology was offered. Then on June 30, 1975, the Department of Education, Culture and Sports approved the offering of the four-year technological curriculum leading to a Bachelor of Science in Fisheries. In SY 1992-1993, additional short-term courses were offered, namely the two-year Food and Beverage Preparation Services and the six-month Basic Seaman Training Course. In 1997-1998, additional two courses were approved by CHED, namely Associate in Hotel and Restaurant Management and Associate in Food Technology, both ladderized programs leading to a bachelor's degree. The Bachelor of Secondary Education with three major areas of specialization was approved in the same school year.
              </p>

              <p>
                In 2001, another milestone was made in the history of the college when it was officially integrated into the Cavite State University System through RA No. 8292, by virtue of Board Resolution No. 2 dated February 6, 2001. The integration of the college, making it CvSU Naic, marked a new beginning. In the same year, Bachelor of Science in Business Management was offered. After a year, the campus also offered Bachelor of Elementary Education. In 2005-2006, two more programs were offered: Bachelor of Science in Information Technology and Bachelor of Computer Science. The integration also broadened the institution's mandated functions by including research, extension, and production.
              </p>

              <p>
                In 2016, the campus started focusing on aggressive infrastructure development. The construction of the AquaBEST Building, the two-storey library, and the STAR Building began. Major repairs of the existing buildings were also done in the following years. The campus continues to target additional academic and student-support facilities including a covered court, dormitory, interfaith chapel, and more laboratories.
              </p>

              <p>
                It is notable that the current University President, Dr. Hernando D. Robles, was the former Campus Administrator of CvSU Naic. He took his oath as the third CvSU President on October 6, 2016.
              </p>
            </div>
          </div>
        </div>

        <div class="accordion-item" data-aos="fade-up" id="vision">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#visionCollapse" aria-expanded="false" aria-controls="visionCollapse">
              Vision & Mission
            </button>
          </h2>
          <div id="visionCollapse" class="accordion-collapse collapse" data-bs-parent="#aboutAccordion">
            <div class="accordion-body rounded-glass soft-panel" style="padding: 1.5rem;">
              <p class="dropcap">
                <strong>Vision:</strong><br /><br />
                The Premier University in historic Cavite globally recognized for excellence in character development, academics, research, innovation, and sustainable community engagement.
              </p>

              <p class="dropcap">
                <strong>Mission:</strong><br /><br />
                Cavite State University shall provide excellent, equitable, and relevant education through quality instruction and responsive research. It shall produce professional, skilled, and morally upright individuals ready for global competitiveness.
              </p>
            </div>
          </div>
        </div>

        <div class="accordion-item" data-aos="fade-up" id="quality">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#qualityCollapse" aria-expanded="false" aria-controls="qualityCollapse">
              Quality Policy
            </button>
          </h2>
          <div id="qualityCollapse" class="accordion-collapse collapse" data-bs-parent="#aboutAccordion">
            <div class="accordion-body rounded-glass soft-panel" style="padding: 1.5rem;">
              <p class="dropcap">
                We commit to the <strong>highest standards of education</strong>, value our stakeholders, strive for <strong>continuous improvement</strong>, and uphold the university's core values of <strong>Truth, Excellence, and Service</strong> to produce globally competitive and morally upright individuals.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="goals" class="mb-5" data-aos="fade-up">
      <h3 class="section-title mb-4">Goals</h3>
      <div class="rounded-glass soft-panel p-4" style="text-align: justify;">
        <p class="dropcap">
          CvSU Naic shall endeavor to achieve the following goals:
        </p>

        <p><strong>1.</strong> Produce technically competent, scientifically oriented graduates with entrepreneurial spirit and strong ethical values.</p>
        <p><strong>2.</strong> Conduct relevant research across fisheries, education, business, IT, arts, and sciences.</p>
        <p><strong>3.</strong> Implement training and outreach programs that foster self-help, critical thinking, and lifelong learning.</p>
        <p><strong>4.</strong> Manage fishery and enterprise projects using sustainable, eco-friendly approaches.</p>
        <p><strong>5.</strong> Build strong linkages with NGOs, government agencies, and communities.</p>
      </div>
    </section>
  </main>

  <footer class="site-footer mt-5">
    <div class="container">
      <div class="row">
        <div class="col-md-3 mb-4">
          <h5>Contact Us</h5>
          <p>
            Cavite State University - Naic Campus<br />
            Brgy. Bucana Malaki, Naic, Cavite, Philippines<br />
            Phone: <a href="tel:+63468905138">(046) 890-5138</a><br />
            Email: <a href="mailto:info@cvsu-naic.edu.ph">info@cvsu-naic.edu.ph</a>
          </p>
        </div>
        <div class="col-md-3 mb-4">
          <h5>Quick Links</h5>
          <ul class="list-unstyled">
            <li><a href="{{ route('login.student') }}">Student Portal</a></li>
            <li><a href="{{ route('login.professor') }}">Teacher Portal</a></li>
            {{-- <li><a href="{{ route('login.admin') }}">Admin Portal</a></li> --}}
            <li><a href="{{ route('enroll.form') }}">Enrollment Form</a></li>
          </ul>
        </div>
        <div class="col-md-3 mb-4">
          <h5>Follow Us</h5>
          <a href="https://www.facebook.com/cvsunaicpio" class="me-3 text-white"><i class="fab fa-facebook fa-lg"></i></a>
          <a href="https://x.com/cavsunaic" class="me-3 text-white"><i class="fab fa-twitter fa-lg"></i></a>
          <a href="https://www.instagram.com/cvsu_naic/" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
        </div>
        <div class="col-md-3 mb-4">
          <h5 class="fw-bold">Calendar</h5>
          <div id="calendar" class="calendar"></div>
        </div>
      </div>
    </div>
  </footer>

  <footer class="sub-footer text-center py-3">
    Copyright &copy; {{ date('Y') }} Cavite State University - Naic Campus LSHS | Brgy. Bucana Malaki, Naic, Cavite, Philippines
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function renderCalendar() {
      const container = document.getElementById('calendar');
      const now = new Date();
      const year = now.getFullYear();
      const month = now.getMonth();
      const today = now.getDate();
      const first = new Date(year, month, 1);
      const startDay = first.getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();
      const monthName = now.toLocaleString('default', { month: 'long' });
      let html = '<div class="month">' + monthName + ' ' + year + '</div>';
      html += '<div class="calendar-grid">';
      const weekNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      for (const weekName of weekNames) {
        html += '<div style="font-weight:700; padding:4px;">' + weekName + '</div>';
      }
      for (let i = 0; i < startDay; i++) {
        html += '<div></div>';
      }
      for (let day = 1; day <= daysInMonth; day++) {
        html += '<div class="' + (day === today ? 'today' : 'day') + '">' + day + '</div>';
      }
      container.innerHTML = html;
    })();

    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
      anchor.addEventListener('click', function (event) {
        const href = this.getAttribute('href');
        if (href.length > 1) {
          event.preventDefault();
          document.querySelector(href)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
  </script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init({
      duration: 800,
      once: true
    });
  </script>

</body>
</html>
