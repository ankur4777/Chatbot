@include('shared.typography')
<style>
/*
 * Scoped to the Filament sign-in layout only. Password reset screens,
 * authenticated dashboards, and all Filament auth actions stay unchanged.
 */
.fi-simple-layout:has(> .shabdd-login-aside) {
    display: grid;
    grid-template-columns: minmax(350px, 44%) minmax(0, 1fr);
    align-items: stretch;
    width: 100%;
    min-height: 100vh;
    min-height: 100dvh;
    background: #f8f9fc;
    font-family: var(--shabdd-font-sans);
}

.fi-simple-layout > .shabdd-login-aside {
    position: relative;
    isolation: isolate;
    min-width: 0;
    overflow: hidden;
    color: #fff;
    background: linear-gradient(155deg, #171b2b 0%, #25293d 68%, #1b2034 100%);
}

.shabdd-login-aside::before {
    content: '';
    position: absolute;
    z-index: -1;
    width: min(44vw, 500px);
    height: min(44vw, 500px);
    right: -30%;
    top: 29%;
    border: 1px solid rgba(255, 255, 255, .09);
    border-radius: 50%;
    box-shadow: 0 0 0 78px rgba(255,255,255,.025), 0 0 0 157px rgba(255,255,255,.018);
    pointer-events: none;
}

.shabdd-login-aside::after {
    content: '';
    position: absolute;
    z-index: -1;
    width: 260px;
    height: 260px;
    bottom: -155px;
    left: -140px;
    background: #f44b54;
    border-radius: 50%;
    filter: blur(80px);
    opacity: .19;
    pointer-events: none;
}

.shabdd-login-aside-inner {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 100vh;
    min-height: 100dvh;
    padding: clamp(32px, 4.5vw, 70px);
}

.shabdd-login-brand {
    display: inline-flex;
    align-items: center;
    gap: 18px;
    width: fit-content;
}

.shabdd-login-brand img {
    display: block;
    width: clamp(142px, 13vw, 180px);
    max-width: 100%;
    height: auto;
    object-fit: contain;
}

.shabdd-login-brand-rule {
    width: 1px;
    height: 28px;
    background: rgba(255,255,255,.18);
}

.shabdd-login-brand-caption {
    font-size: .76rem;
    font-weight: 600;
    color: #d3d9e5;
    letter-spacing: .02em;
}

.shabdd-login-story {
    max-width: 500px;
    padding-block: 46px;
}

.shabdd-login-kicker {
    margin: 0 0 20px;
    color: #ff8b90;
    font-size: .76rem;
    font-weight: 700;
    letter-spacing: .13em;
}

.shabdd-login-story h2 {
    margin: 0;
    max-width: 510px;
    color: #fff;
    font-weight: 700;
    font-size: clamp(2rem, 3.35vw, 3.3rem);
    letter-spacing: -.025em;
    line-height: 1.12;
}

.shabdd-login-description {
    max-width: 430px;
    margin: 21px 0 30px;
    color: #c1c9d8;
    font-size: .96rem;
    line-height: 1.75;
}

.shabdd-login-benefits {
    display: grid;
    gap: 14px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.shabdd-login-benefits li {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #eff1f7;
    font-size: .88rem;
}

.shabdd-login-check {
    display: grid;
    place-items: center;
    flex: 0 0 25px;
    height: 25px;
    border-radius: 50%;
    background: rgba(249,79,89,.15);
    color: #ff737b;
    font-size: .85rem;
    font-weight: 700;
}

.shabdd-login-aside-bottom {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #9da8ba;
    font-size: .75rem;
    letter-spacing: .01em;
}

.shabdd-login-bottom-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #f64d57;
}

.fi-simple-layout:has(> .shabdd-login-aside) > .fi-simple-main-ctn {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 0;
    min-height: 100vh;
    min-height: 100dvh;
    padding: 34px clamp(18px, 5vw, 80px);
    background: #f8f9fc;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-simple-main {
    width: 100%;
    max-width: 475px !important;
    margin: 0 auto;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-simple-page {
    padding: clamp(24px, 3.4vw, 43px);
    background: #fff;
    border: 1px solid #e7eaf1;
    border-radius: 20px;
    box-shadow: 0 15px 60px rgba(27,37,58,.055);
    color: #20283b;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-simple-page-content {
    gap: 23px;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-simple-header {
    text-align: left;
    align-items: flex-start;
    gap: 6px;
    margin-bottom: 14px;
}

/* The graphic appears on the branded left panel instead of repeating here. */
.fi-simple-layout:has(> .shabdd-login-aside) .fi-simple-header .fi-logo {
    display: none !important;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-simple-header-heading {
    color: #20283b;
    font-weight: 700;
    letter-spacing: -.025em;
    font-size: clamp(1.65rem, 2.35vw, 2.12rem);
    line-height: 1.15;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-simple-header-subheading {
    color: #6b7587;
    font-size: .85rem;
    line-height: 1.5;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-fo-field-wrp-label,
.fi-simple-layout:has(> .shabdd-login-aside) .fi-fo-field-wrp-label > span {
    color: #303a4d;
    font-weight: 600;
    font-size: .85rem;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-input-wrp {
    background: #f9fafc !important;
    border: 1px solid #e5e9f1 !important;
    box-shadow: none !important;
    border-radius: 10px !important;
    min-height: 45px;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-input-wrp:focus-within {
    border-color: #ef7178 !important;
    box-shadow: 0 0 0 3px rgba(247,77,87,.13) !important;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-input {
    color: #263045;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-btn {
    min-height: 44px;
    border-radius: 10px !important;
    font-weight: 600;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-btn.fi-color-primary {
    background: #f14b55 !important;
    color: #fff !important;
    box-shadow: none !important;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-btn.fi-color-primary:hover {
    background: #df3e49 !important;
}

.fi-simple-layout:has(> .shabdd-login-aside) .fi-link {
    color: #de414c;
    font-weight: 600;
}


/* Friendly AI assistant illustration: pure SVG + CSS, no scripts or dependencies. */
.shabdd-login-story {
    padding-block: 22px;
}
.shabdd-login-description {
    margin-bottom: 19px;
}
.shabdd-login-showcase {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    width: 100%;
}
.shabdd-login-showcase .shabdd-login-benefits {
    flex: 1;
    min-width: 170px;
}
.shabdd-login-mascot {
    position: relative;
    isolation: isolate;
    flex: 0 1 188px;
    width: clamp(140px, 16vw, 188px);
    max-width: 47%;
    margin-inline: auto 0;
    padding-top: 24px;
    pointer-events: none;
}
.shabdd-login-mascot-halo {
    position: absolute;
    z-index: -1;
    left: 5%;
    right: 5%;
    top: 18%;
    aspect-ratio: 1;
    background: radial-gradient(circle, rgba(255,106,118,.20), rgba(117,104,242,.09) 45%, transparent 74%);
    animation: shabdd-bot-glow 4.8s ease-in-out infinite;
}
.shabdd-login-robot {
    display: block;
    width: 100%;
    height: auto;
    overflow: visible;
    filter: drop-shadow(0 15px 13px rgba(8,12,28,.28));
    transform-origin: center 80%;
    animation: shabdd-bot-float 4s ease-in-out infinite;
}
.shabdd-robot-eyes {
    transform-box: fill-box;
    transform-origin: center;
    animation: shabdd-bot-blink 5.4s ease-in-out infinite;
}
.shabdd-robot-antenna {
    transform-box: fill-box;
    transform-origin: center;
    animation: shabdd-bot-antenna 3.4s ease-in-out infinite;
}
.shabdd-robot-arm-right {
    transform-box: fill-box;
    transform-origin: 28% 80%;
    animation: shabdd-bot-wave 3.8s ease-in-out infinite;
}
.shabdd-login-mascot-message {
    position: absolute;
    z-index: 2;
    top: 0;
    right: -7px;
    display: inline-flex;
    gap: 7px;
    align-items: center;
    padding: 9px 11px;
    border: 1px solid #e3e9f4;
    border-radius: 13px 13px 13px 3px;
    background: #fff;
    color: #293247;
    box-shadow: 0 8px 22px rgba(9,15,36,.22);
    white-space: nowrap;
    font-size: .75rem;
    font-weight: 700;
    animation: shabdd-bot-speech 4s ease-in-out infinite;
}
.shabdd-login-mascot-dot {
    flex: 0 0 7px;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #13b987;
    box-shadow: 0 0 0 3px rgba(19,185,135,.13);
}
.shabdd-login-mascot-caption {
    display: block;
    margin-top: -3px;
    color: #aebbd0;
    font-size: .75rem;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
}
@keyframes shabdd-bot-float {
    0%, 100% { transform: translateY(0) rotate(-1deg); }
    50% { transform: translateY(-9px) rotate(1deg); }
}
@keyframes shabdd-bot-blink {
    0%, 43%, 48%, 100% { transform: scaleY(1); }
    45%, 46% { transform: scaleY(.08); }
}
@keyframes shabdd-bot-wave {
    0%, 25%, 65%, 100% { transform: rotate(0deg); }
    37%, 52% { transform: rotate(-13deg); }
    44% { transform: rotate(6deg); }
}
@keyframes shabdd-bot-antenna {
    0%, 100% { opacity: .9; }
    50% { opacity: .5; }
}
@keyframes shabdd-bot-glow {
    0%, 100% { opacity: .65; transform: scale(.95); }
    50% { opacity: 1; transform: scale(1.1); }
}
@keyframes shabdd-bot-speech {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}

@media (max-width: 960px) {
    .fi-simple-layout:has(> .shabdd-login-aside) {
        grid-template-columns: 1fr;
    }
    .shabdd-login-aside-inner {
        min-height: auto;
        padding: 24px clamp(22px, 5vw, 45px);
        gap: 9px;
    }
    .shabdd-login-brand img {
        width: 140px;
    }
    .shabdd-login-brand-rule,
    .shabdd-login-brand-caption,
    .shabdd-login-benefits,
    .shabdd-login-aside-bottom {
        display: none;
    }
    .shabdd-login-story {
        padding: 4px 0 0;
    }
    .shabdd-login-kicker {
        margin-bottom: 4px;
        font-size: .75rem;
    }
    .shabdd-login-story h2 {
        font-size: clamp(1.15rem, 3vw, 1.5rem);
        line-height: 1.25;
        letter-spacing: -.02em;
    }
    .shabdd-login-description {
        display: none;
    }
    .fi-simple-layout:has(> .shabdd-login-aside) > .fi-simple-main-ctn {
        min-height: auto;
        padding: 28px 20px 45px;
    }
}

@media (max-width: 560px) {
    .shabdd-login-aside-inner {
        padding: 20px;
    }
    .fi-simple-layout:has(> .shabdd-login-aside) > .fi-simple-main-ctn {
        padding: 20px 14px 34px;
    }
    .fi-simple-layout:has(> .shabdd-login-aside) .fi-simple-page {
        padding: 24px 20px;
        border-radius: 16px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .fi-simple-layout:has(> .shabdd-login-aside) *,
    .fi-simple-layout:has(> .shabdd-login-aside) *::before,
    .fi-simple-layout:has(> .shabdd-login-aside) *::after {
        transition: none !important;
        animation: none !important;
    }
}

@media (max-width: 960px) {
    .shabdd-login-story {
        position: relative;
        padding-right: 100px;
    }
    .shabdd-login-showcase {
        position: absolute;
        top: -16px;
        right: 0;
        width: 95px;
        margin: 0;
    }
    .shabdd-login-mascot {
        flex: none;
        width: 95px;
        max-width: none;
        padding-top: 0;
    }
    .shabdd-login-mascot-message,
    .shabdd-login-mascot-caption {
        display: none;
    }
}
@media (max-width: 560px) {
    .shabdd-login-story {
        padding-right: 77px;
    }
    .shabdd-login-showcase {
        top: -2px;
        width: 74px;
    }
    .shabdd-login-mascot {
        width: 74px;
    }
}

</style>
