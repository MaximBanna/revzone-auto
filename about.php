<?php require('nav.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>About | RevZone Auto</title>

    <link rel="stylesheet" href="aboutStyle.css">
</head>

<body>
    <?php require('header.php'); ?>
    <!-- HERO -->
    <header class="about-hero">
        <div class="hero-bg">
            <div class="grid"></div>
            <div class="glow"></div>
            <div class="speedlines"></div>
        </div>

        <div class="hero-wrap">
            <div class="hero-left">
                <div class="badge">
                    <span class="dot"></span>
                    <span>REVZONE // ABOUT</span>
                </div>

                <h1 class="hero-title">
                    Precision parts.<br />
                    <span class="muted">Real performance.</span>
                </h1>

                <p class="hero-sub">
                    RevZone Auto is built for drivers who care about detail — from daily reliability to track-ready upgrades.
                    Curated parts, clean fitment, fast handling, zero noise.
                </p>

                <div class="hero-cta">
                    <a class="btn primary" href="products.php">
                        Explore Products
                        <span class="btn-glint"></span>
                    </a>
                    <a class="btn ghost" href="contact.php">Talk to Us</a>
                </div>

                <div class="hero-kpis">
                    <div class="kpi">
                        <div class="kpi-top">
                            <span class="kpi-icon">⚙</span>
                            <span class="kpi-label">Curated Inventory</span>
                        </div>
                        <div class="kpi-value">Quality-first</div>
                    </div>

                    <div class="kpi">
                        <div class="kpi-top">
                            <span class="kpi-icon">🧰</span>
                            <span class="kpi-label">Fitment Focus</span>
                        </div>
                        <div class="kpi-value">Less guessing</div>
                    </div>

                    <div class="kpi">
                        <div class="kpi-top">
                            <span class="kpi-icon">🚚</span>
                            <span class="kpi-label">Fast Delivery</span>
                        </div>
                        <div class="kpi-value">Quick turnaround</div>
                    </div>
                </div>
            </div>

            <div class="hero-right">
                <div class="showcase">
                    <div class="showcase-frame">
                        <div class="showcase-title">
                            <span class="chip">REVZONE SYSTEM</span>
                            <span class="status"><span class="pulse"></span> Online</span>
                        </div>

                        <div class="rings">
                            <div class="ring r1"></div>
                            <div class="ring r2"></div>
                            <div class="ring r3"></div>
                        </div>

                        <div class="parts">
                            <button class="part-card" type="button">
                                <span class="p-top">
                                    <span class="p-dot"></span>
                                    <span class="p-name">Turbo / Intake</span>
                                </span>
                                <span class="p-desc">Airflow upgrades, clean gains.</span>
                                <span class="p-hover">Hover for torque ⚡</span>
                            </button>

                            <button class="part-card" type="button">
                                <span class="p-top">
                                    <span class="p-dot"></span>
                                    <span class="p-name">Brakes</span>
                                </span>
                                <span class="p-desc">Confidence on every stop.</span>
                                <span class="p-hover">Hover for grip 🛑</span>
                            </button>

                            <button class="part-card" type="button">
                                <span class="p-top">
                                    <span class="p-dot"></span>
                                    <span class="p-name">Suspension</span>
                                </span>
                                <span class="p-desc">Control the road feel.</span>
                                <span class="p-hover">Hover for handling 🏁</span>
                            </button>
                        </div>
                        <div class="scanline"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- MARQUEE STRIP -->
    <section class="marquee">
        <div class="marquee-track">
            <span>OEM+</span><i></i>
            <span>PERFORMANCE</span><i></i>
            <span>RELIABILITY</span><i></i>
            <span>FITMENT</span><i></i>
            <span>FAST SHIPPING</span><i></i>

            <span>OEM+</span><i></i>
            <span>PERFORMANCE</span><i></i>
            <span>RELIABILITY</span><i></i>
            <span>FITMENT</span><i></i>
            <span>FAST SHIPPING</span><i></i>
        </div>
    </section>

    <!-- WHY REVZONE -->
    <main class="about-main">
        <section class="section">
            <div class="section-head">
                <h2>Why RevZone exists</h2>
                <p>
                    Buying car parts online shouldn’t feel like gambling. We built RevZone to make upgrades feel
                    confident, clean, and satisfying — like a perfect install.
                </p>
            </div>

            <div class="feature-grid">
                <article class="feature-card">
                    <div class="fc-top">
                        <span class="fc-icon">🔍</span>
                        <h3>Curated, not cluttered</h3>
                    </div>
                    <p>Only parts we’d put on our own cars — balanced between value, quality, and proven demand.</p>
                    <div class="fc-bar"><span></span></div>
                </article>

                <article class="feature-card">
                    <div class="fc-top">
                        <span class="fc-icon">🧪</span>
                        <h3>Built for fitment</h3>
                    </div>
                    <p>We care about compatibility, not just product pages. Less mismatch, more installs.</p>
                    <div class="fc-bar"><span></span></div>
                </article>

                <article class="feature-card">
                    <div class="fc-top">
                        <span class="fc-icon">🧠</span>
                        <h3>Support that speaks car</h3>
                    </div>
                    <p>We keep it simple: clear answers, quick help, and no “copy-paste” replies.</p>
                    <div class="fc-bar"><span></span></div>
                </article>

                <article class="feature-card">
                    <div class="fc-top">
                        <span class="fc-icon">⚡</span>
                        <h3>Fast flow, smooth checkout</h3>
                    </div>
                    <p>Designed like a premium pit-lane: quick access, clean UI, and a calm purchase experience.</p>
                    <div class="fc-bar"><span></span></div>
                </article>
            </div>
        </section>

        <!-- INTERACTIVE TIMELINE -->
        <section class="section">
            <div class="section-head">
                <h2>How we work</h2>
                <p>Our process is simple — and we made it look like a “build pipeline” because that’s the RevZone vibe.</p>
            </div>

            <div class="pipeline">
                <div class="pipe-line"></div>

                <div class="step">
                    <div class="step-node"><span class="node-core"></span></div>
                    <div class="step-card">
                        <h3>1) Select</h3>
                        <p>Pick your part with clear categories and clean product info.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-node"><span class="node-core"></span></div>
                    <div class="step-card">
                        <h3>2) Verify</h3>
                        <p>We focus on fitment signals and reduce mismatch scenarios.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-node"><span class="node-core"></span></div>
                    <div class="step-card">
                        <h3>3) Ship</h3>
                        <p>Fast processing and clear order status so you’re never guessing.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-node"><span class="node-core"></span></div>
                    <div class="step-card">
                        <h3>4) Install & Enjoy</h3>
                        <p>That “new parts” feeling — without the stress.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>

</html>