<!DOCTYPE html>
<html class="no-js" lang="en">

<head>

    <!--- basic page needs
    ================================================== -->
    <meta charset="utf-8">
    <title>Martease.</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- mobile specific metas
    ================================================== -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS
    ================================================== -->
    <link rel="stylesheet" href="{{asset('landing/css/vendor.css')}}">
    <link rel="stylesheet" href="{{asset('landing/css/styles.css')}}">

    <!-- favicons
    ================================================== -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('landing/images/apple-touch-icon.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('landing/images/favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('landing/images/favicon-16x16.png')}}">
    <link rel="manifest" href="site.webmanifest">

</head>

<body id="top" class="ss-preload theme-particles">


    <!-- preloader
    ================================================== -->
    <div id="preloader">
        <div id="loader" class="dots-fade">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>


    <!-- intro
    ================================================== -->
    <section id="intro" class="s-intro">

        <div id="particles-js" class="s-intro__particles"></div>

        <header class="s-intro__header">
            <div class="s-intro__logo">
                <a class="logo" href="#">
                    <span style="color: beige;">Martease.</span>
                </a>
            </div>
        </header> <!-- s-intro__header -->

        <div class="row s-intro__content">
            <div class="column">

                <div class="text-pretitle">
                    Nice to meet you.
                </div>

                <h1 class="text-huge-title">
                    We are preparing <br>
                    something exciting <br>
                    & amazing for you.
                </h1>

                <div class="s-intro__content-bottom">

                    <div class="s-intro__content-bottom-block">
                        <h5>Launching in</h5>

                        <div class="counter">
                            <div class="counter__time">
                                <span class="ss-days">00</span>
                                <span>D</span>
                            </div>
                            <div class="counter__time">
                                <span class="ss-hours">00</span>
                                <span>H</span>
                            </div>
                            <div class="counter__time minutes">
                                <span class="ss-minutes">00</span>
                                <span>M</span>
                            </div>
                            <div class="counter__time">
                                <span class="ss-seconds">00</span>
                                <span>S</span>
                            </div>
                        </div> <!-- end counter -->

                    </div> <!-- end s-intro-content__bottom-block -->

                    <div class="s-intro__content-bottom-block">

                        <h5>Follow Us</h5>

                        <ul class="social">
                            <li><a href="https://www.facebook.com">FB</a></li>
                            <li><a href="">TW</a></li>
                            <li><a href="">IG</a></li>
                            <li><a href="">DB</a></li>
                            <li><a href="">BH</a></li>
                        </ul>

                    </div> <!-- end s-intro-content__bottom-block -->

                </div> <!-- end s-intro-content__bottom -->

            </div>
        </div> <!-- s-intro__content -->

        <div class="s-intro__notify">
            <button type="button" class="btn--stroke btn--small ss-modal-trigger">
                Notify Me
            </button>
        </div> <!-- s-intro__notify -->

        <div hidden class="s-intro__modal ss-modal">
            <div class="ss-modal__inner">

                <span class="ss-modal__close"></span>

                <svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" width="30" height="30">
                    <path d="M.5 4.5l7 4 7-4m-13-3h12a1 1 0 011 1v10a1 1 0 01-1 1h-12a1 1 0 01-1-1v-10a1 1 0 011-1z"
                        stroke="var(--color-2-dark)"></path>
                </svg>

                <h4>Sign Up</h4>

                <p class="ss-modal__text">
                    Be the first to know about the latest updates and
                    get exclusive offer on our grand opening.
                </p>

                <form id="mc-form" class="mc-form">
                    <input type="email" name="EMAIL" id="mce-EMAIL" class="u-fullwidth text-center"
                        placeholder="Email Address"
                        title="The domain portion of the email address is invalid (the portion after the @)."
                        pattern="^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*(\.\w{2,})+$"
                        required>
                    <input type="submit" name="subscribe" value="Subscribe" class="btn--small btn--primary u-fullwidth">
                    <!-- <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_cdb7b577e41181934ed6a6a44_9a91cfe7b3" tabindex="-1" value=""></div> -->
                    <div class="mc-status"></div>
                </form>

            </div> <!-- end ss-modal__inner -->
        </div> <!-- end ss-modal -->

        <div class="s-intro__scroll">
            <a href="#hidden" class="smoothscroll">
                Scroll For More
            </a>
        </div> <!-- s-intro__scroll -->

    </section> <!-- end s-intro -->


    <!-- hidden element
    ================================================== -->
    <div id="hidden" aria-hidden="true" style="opacity: 0;"></div>


    <!-- details
    ================================================== -->
    <section id="details" class="s-details">

        <div class="row">
            <div class="column">

                <h1 class="text-huge-title text-center">
                    Hi, We Are Martease.
                </h1>

                <nav class="tab-nav">
                    <ul class="tab-nav__list">
                        <li class="active" data-id="tab-about">
                            <a href="#0">
                                <span>About</span>
                            </a>
                        </li>
                        <li>
                            <a href="#tab-services">
                                <span>Services</span>
                            </a>
                        </li>
                        <li>
                            <a href="#tab-contact">
                                <span>Contact</span>
                            </a>
                        </li>
                    </ul>
                </nav> <!-- end tab-nav -->

                <div class="tab-content">

                    <!-- 01 - tab about -->
                    <div id="tab-about" class='tab-content__item'>

                        <div class="row tab-content__item-header">
                            <div class="column">
                                <h2>Our Story.</h2>
                            </div>
                        </div>

                        <div class="row">
                            <div class="column">
                                <p class="lead">
                                    Martease was born out of a vision to empower small shop vendors by bridging the gap
                                    between buyers and sellers in the digital world. We understand that independent
                                    businesses often struggle to gain online visibility, and that’s where we step in.
                                    Our platform provides a seamless, user-friendly marketplace where vendors can
                                    showcase their products, and buyers can explore a diverse range of offerings, all in
                                    one place.
                                </p>


                                <div class="row">
                                    <div class="column lg-6 tab-12">
                                        <h4>More About Us.</h4>
                                        <p>
                                            At Martease, we believe in fostering a thriving ecosystem where local
                                            businesses can compete with larger retailers while maintaining their unique
                                            charm. By integrating smart technology with a personalized shopping
                                            experience, we ensure that vendors reach the right customers and buyers
                                            discover high-quality products with ease. Whether you’re a seller looking to
                                            expand your reach or a buyer searching for authentic local goods, Martease
                                            is your trusted partner in the online marketplace revolution.
                                        </p>
                                    </div>
                                    <div class="column lg-6 tab-12">
                                        <h4>Need More Details?</h4>
                                        <p>
                                            Need a great reliable website?
                                            We highly recommend <a href="#">Martease</a>.
                                            Powerful web and Wordpress hosting. Guaranteed. Starting at minimum price
                                            per month.
                                        </p>
                                        <a href="#" class="btn btn--stroke u-fullwidth">Get Started Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- end 01 - tab about -->

                    <!-- 02 - tab services -->
                    <div id="tab-services" class='tab-content__item'>

                        <div class="row tab-content__item-header">
                            <div class="column">
                                <h2>What We Do.</h2>
                            </div>
                        </div>

                        <div class="row">
                            <div class="column">
                                <p class="lead">
                                    At Martease, we help small shop vendors establish a strong online presence by
                                    creating customized websites that attract more buyers. In today’s digital era,
                                    having an online storefront is essential for growth, and we make it easy for sellers
                                    to showcase their products, engage with customers, and drive more sales.
                                </p>
                                <p class="lead">Our goal is to empower vendors with beautifully designed, user-friendly
                                    websites that not only highlight their unique offerings but also enhance their
                                    credibility in the market. By providing seamless e-commerce solutions, we ensure
                                    that small businesses can compete with larger retailers while building lasting
                                    relationships with their customers. With Martease, sellers can take their businesses
                                    to the next level and thrive in the digital marketplace.</p>
                            </div>
                        </div>

                        <div class="row services-list block-lg-one-half block-md-one-half block-tab-whole">

                            <div class="column services-list__item">
                                <div class="services-list__item-content">
                                    <h4 class="item-title">Brand Identity</h4>
                                    <p>
                                        At Martease, we help businesses craft a compelling brand identity that resonates
                                        with their audience. From logo design to color schemes and typography, we ensure
                                        that every element aligns with your brand’s values and personality. A strong
                                        identity builds trust and recognition, helping small vendors stand out in a
                                        competitive market.


                                    </p>
                                </div>
                            </div>

                            <div class="column services-list__item">
                                <div class="services-list__item-content">
                                    <h4 class="item-title">Illustration</h4>
                                    <p>
                                        Visual storytelling is key to capturing customer attention, and our expert
                                        illustrators bring your brand to life through creative, custom artwork. Whether
                                        it’s for product promotions, website visuals, or social media content, we design
                                        engaging illustrations that enhance your brand’s aesthetic and communicate your
                                        message effectively.
                                    </p>
                                </div>
                            </div>

                            <div class="column services-list__item">
                                <div class="services-list__item-content">
                                    <h4 class="item-title">Web Design</h4>
                                    <p>
                                        A great online presence starts with an intuitive and visually appealing website.
                                        We specialize in designing responsive, user-friendly websites that not only look
                                        stunning but also provide seamless navigation for customers. Our goal is to
                                        create digital storefronts that attract buyers and boost sales for small
                                        vendors.
                                    </p>
                                </div>
                            </div>

                            <div class="column services-list__item">
                                <div class="services-list__item-content">
                                    <h4 class="item-title">Product Strategy</h4>
                                    <p>
                                        Success in e-commerce requires a solid product strategy. We help vendors
                                        position their products effectively by analyzing market trends, optimizing
                                        product presentations, and enhancing customer experience. From pricing
                                        strategies to inventory management, we ensure that sellers maximize their reach
                                        and revenue.
                                    </p>
                                </div>
                            </div>

                            <div class="column services-list__item">
                                <div class="services-list__item-content">
                                    <h4 class="item-title">UI/UX Design</h4>
                                    <p>
                                        User experience is at the heart of what we do. We focus on creating intuitive
                                        interfaces that enhance customer interactions, ensuring that every website we
                                        build is easy to navigate and visually appealing. By prioritizing smooth and
                                        engaging user experiences, we help businesses convert visitors into loyal
                                        customers.
                                    </p>
                                </div>
                            </div>

                            <div class="column services-list__item">
                                <div class="services-list__item-content">
                                    <h4 class="item-title">E-Commerce</h4>
                                    <p>
                                        Our expertise in e-commerce solutions empowers small vendors to sell online with
                                        ease. From setting up product listings to secure payment integrations and mobile
                                        optimization, we provide all the tools needed for a successful online store. We
                                        simplify the process so vendors can focus on what they do best—selling great
                                        products.
                                    </p>
                                </div>
                            </div>

                        </div> <!-- end services-list -->

                    </div> <!-- end 02 - tab services -->

                    <!-- 03 - tab contact -->
                    <div id="tab-contact" class="tab-content__item">

                        <div class="row tab-content__item-header">
                            <div class="column">
                                <h2>Get in Touch.</h2>
                            </div>
                        </div>

                        <div class="row">
                            <div class="column">

                                <p class="lead">
                                    Have questions or want to collaborate? We’d love to hear from you! Reach out to us,
                                    and let’s build something amazing together.
                                </p>

                                <div class="row">
                                    <div class="column lg-6 tab-12">
                                        <h4>Where to Find Us</h4>

                                        <p>
                                            AT MADHUBANI PO-BIJAYEE PS<br>
                                            PURBA CHAMPARAN,<br>
                                            GHORSAHAN, Bihar, India<br> 
                                            845315


                                        </p>

                                    </div>

                                    <div class="column lg-6 tab-12">
                                        <h4>Follow Us</h4>

                                        <ul class="link-list">
                                            <li><a href="#0">Facebook</a></li>
                                            <li><a href="#0">Twitter</a></li>
                                            <li><a href="#0">Instagram</a></li>
                                        </ul>

                                    </div>
                                </div>

                                <p class="tab-content__item-bottom">
                                    <a href="mailto:support@martease.in" class="contact-email">support@martease.in</a>
                                    <span class="contact-number">
                                        <a href="tel:+917257832941">+91 7257832941</a>
                                        <!-- <span>/</span>
                                        <a href="tel:123-456-9000">+123 456 9000</a> -->
                                    </span>
                                </p>

                            </div>
                        </div>

                    </div> <!-- end 03 - tab contact -->

                </div> <!-- end tab content -->

                <!-- footer  -->
                <footer>
                    <div class="ss-copyright">
                        <span>© Copyright Martease. 2025</span>
                        <span>Design by <a href="https://github.com/ritshkr1">Ritesh</a></span>
                    </div>
                </footer>

            </div>
        </div>

        <div class="ss-go-top">
            <a class="smoothscroll" title="Back to Top" href="#top">
                <span>Back to Top</span>
                <svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" width="26" height="26">
                    <path
                        d="M7.5 1.5l.354-.354L7.5.793l-.354.353.354.354zm-.354.354l4 4 .708-.708-4-4-.708.708zm0-.708l-4 4 .708.708 4-4-.708-.708zM7 1.5V14h1V1.5H7z"
                        fill="currentColor"></path>
                </svg>
            </a>
        </div> <!-- end ss-go-top -->

    </section> <!-- end s-details -->


    <!-- Java Script
    ================================================== -->
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>
    <script src="js/particles.min.js"></script>
    <script src="js/particle-settings.js"></script>

</body>

</html>