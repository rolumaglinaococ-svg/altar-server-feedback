<?php
/**
 * feedback.php
 * The public feedback form. No login or account is required.
 * Anyone with this link can fill it out and submit.
 *
 * NOTE ON THIS REDESIGN: every <input>/<textarea> keeps the exact same
 * `name` attribute as before (parish_vicariate, overall_rating,
 * enjoyed_most, etc.), so submit_feedback.php and the database did not
 * need to change at all. Only the layout, wording, and interactivity
 * were improved.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Your Experience - Altar Server Month 2026</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="hero">
        <div class="hero-inner">
            <span class="badge">🙏 ALTAR SERVER MONTH</span>
            <h1>ALTAR SERVER MONTH 2026</h1>
            <p class="theme">&ldquo;"St.Tarcisius: Called to Serve, Guided by Christ."&rdquo;</p>
            <div class="meta-row">
                <span>⛪ St. Peregrine Quasi Parish</span>
                <span>📅 August 29&ndash;30, 2026</span>
            </div>
            <p class="verse">&ldquo;Serve the Lord with gladness.&rdquo; &mdash; Psalm 100:2</p>
        </div>
    </div>

    <!-- Progress indicator: updates as the visitor scrolls through sections -->
    <div class="progress-wrap">
        <div class="progress-label">
            <span id="progress-step-label">About You</span>
            <span id="progress-step-count">Step 1 of 5</span>
        </div>
        <div class="progress-track">
            <div class="progress-fill" id="progress-fill"></div>
        </div>
    </div>

    <div class="container">

        <div class="card intro-card">
            <div class="intro-emoji">💙</div>
            <p>Thank you for celebrating Altar Server Month with us! We want to hear about your experience &mdash; your thoughts, suggestions, and memories can help us make our next activities even better. This will only take a few minutes.</p>
            <span class="intro-note">🔓 No login required &mdash; just fill this out and go</span>
        </div>

        <div class="card">
            <div class="section-heading"><span class="icon">📅</span> Event Schedule</div>
            <p class="section-sub">The final program may still be updated</p>
            <div class="schedule-grid">
                <div class="schedule-day">
                    <div class="day-label">Day 1</div>
                    <div class="day-date">August 29, 2026</div>
                    <div class="day-time">12:30 PM &ndash; 6:00 PM</div>
                </div>
                <div class="schedule-day">
                    <div class="day-label">Day 2</div>
                    <div class="day-date">August 30, 2026</div>
                    <div class="day-time">12:30 PM &ndash; 5:00 PM</div>
                </div>
            </div>
        </div>

        <div id="form-message" class="alert" style="display:none;"></div>

        <form id="feedback-form" class="card" novalidate>

            <!-- ============ SECTION: ABOUT YOU ============ -->
            <div class="form-section" data-section="about">
                <div class="section-heading"><span class="icon">👤</span> About You</div>
                <p class="section-sub">Just the basics &mdash; anonymous is okay too</p>

                <div class="form-group" data-group="name">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" maxlength="100" placeholder="Ex: Rojemel ">
                    <span class="hint">Optional &mdash; you may leave this blank if you prefer to share your thoughts anonymously.</span>
                </div>

                <div class="form-group" data-group="parish_vicariate">
                    <label for="parish_vicariate">Parish / Vicariate<span class="required-mark">*</span></label>
                    <input type="text" id="parish_vicariate" name="parish_vicariate" maxlength="150" placeholder="Chapel">
                    <span class="field-error" style="display:none;"></span>
                </div>
            </div>

            <!-- ============ SECTION: YOUR EXPERIENCE ============ -->
            <div class="form-section" data-section="experience" style="margin-top:36px;">
                <div class="section-heading"><span class="icon">⭐</span> Your Experience</div>
                <p class="section-sub">Tell us how the event felt for you</p>

                <div class="form-group" data-group="overall_rating">
                    <label>How would you rate your overall experience?<span class="required-mark">*</span></label>

                    <div class="star-rating-widget">
                        <div class="star-row" id="star-row">
                            <button type="button" class="star-btn" data-value="1" aria-label="Rate 1 out of 5">☆</button>
                            <button type="button" class="star-btn" data-value="2" aria-label="Rate 2 out of 5">☆</button>
                            <button type="button" class="star-btn" data-value="3" aria-label="Rate 3 out of 5">☆</button>
                            <button type="button" class="star-btn" data-value="4" aria-label="Rate 4 out of 5">☆</button>
                            <button type="button" class="star-btn" data-value="5" aria-label="Rate 5 out of 5">☆</button>
                        </div>
                        <div class="star-feedback-text" id="star-feedback-text" aria-live="polite">Tap a star to rate your experience</div>
                        <!-- The real value PHP receives. Kept as the same name="overall_rating" as before. -->
                        <input type="hidden" name="overall_rating" id="overall_rating" value="">
                    </div>
                    <span class="field-error" style="display:none;"></span>
                </div>

                <div class="form-group" data-group="enjoyed_most">
                    <label for="enjoyed_most">What made this event memorable for you?<span class="required-mark">*</span></label>
                    <textarea id="enjoyed_most" name="enjoyed_most" maxlength="2000" placeholder="Tell us about your experience! 💙"></textarea>
                    <span class="field-error" style="display:none;"></span>
                </div>

                <div class="form-group" data-group="favorite_activity">
                    <label for="favorite_activity">What part of the event did you enjoy the most?<span class="required-mark">*</span></label>
                    <textarea id="favorite_activity" name="favorite_activity" maxlength="1000" placeholder="Share your favorite moment"></textarea>
                    <span class="field-error" style="display:none;"></span>
                </div>

                <div class="form-group" data-group="feeling">
                    <label>How did you feel while participating in the event?<span class="required-mark">*</span></label>
                    <div class="choice-group">
                        <div class="choice-option">
                            <input type="radio" id="f1" name="feeling" value="Very happy">
                            <label for="f1"><span class="emoji">😊</span> Very happy</label>
                        </div>
                        <div class="choice-option">
                            <input type="radio" id="f2" name="feeling" value="Happy">
                            <label for="f2"><span class="emoji">😄</span> Happy</label>
                        </div>
                        <div class="choice-option">
                            <input type="radio" id="f3" name="feeling" value="It was okay">
                            <label for="f3"><span class="emoji">🙂</span> It was okay</label>
                        </div>
                        <div class="choice-option">
                            <input type="radio" id="f4" name="feeling" value="Not very comfortable">
                            <label for="f4"><span class="emoji">😐</span> Not very comfortable</label>
                        </div>
                        <div class="choice-option">
                            <input type="radio" id="f5" name="feeling" value="Not satisfied">
                            <label for="f5"><span class="emoji">😔</span> Not satisfied</label>
                        </div>
                    </div>
                    <span class="field-error" style="display:none;"></span>
                </div>

                <div class="form-group" data-group="met_other_servers">
                    <label>Did you get the chance to meet other altar servers?<span class="required-mark">*</span></label>
                    <div class="choice-group">
                        <div class="choice-option">
                            <input type="radio" id="m1" name="met_other_servers" value="Yes, and I really enjoyed it!">
                            <label for="m1">Yes, and I really enjoyed it!</label>
                        </div>
                        <div class="choice-option">
                            <input type="radio" id="m2" name="met_other_servers" value="Yes, a little">
                            <label for="m2">Yes, a little</label>
                        </div>
                        <div class="choice-option">
                            <input type="radio" id="m3" name="met_other_servers" value="Not really">
                            <label for="m3">Not really</label>
                        </div>
                    </div>
                    <span class="field-error" style="display:none;"></span>
                </div>
            </div>

            <!-- ============ SECTION: FAITH & SERVICE ============ -->
            <div class="form-section" data-section="faith" style="margin-top:36px;">
                <div class="section-heading"><span class="icon">✝️</span> Faith &amp; Service</div>
                <p class="section-sub">A little reflection on your journey as a server</p>

                <div class="form-group" data-group="learned_realized">
                    <label for="learned_realized">What is one thing you learned or realized?<span class="required-mark">*</span></label>
                    <textarea id="learned_realized" name="learned_realized" maxlength="2000" placeholder="Share what touched your heart"></textarea>
                    <span class="field-error" style="display:none;"></span>
                </div>

                <div class="form-group" data-group="appreciated_service">
                    <label>Did this event help you appreciate your service as an altar server more?<span class="required-mark">*</span></label>
                    <div class="choice-group">
                        <div class="choice-option">
                            <input type="radio" id="a1" name="appreciated_service" value="Yes, definitely!">
                            <label for="a1">Yes, definitely! 🙏</label>
                        </div>
                        <div class="choice-option">
                            <input type="radio" id="a2" name="appreciated_service" value="Yes, a little">
                            <label for="a2">Yes, a little</label>
                        </div>
                        <div class="choice-option">
                            <input type="radio" id="a3" name="appreciated_service" value="I'm not sure">
                            <label for="a3">I'm not sure</label>
                        </div>
                        <div class="choice-option">
                            <input type="radio" id="a4" name="appreciated_service" value="Not really">
                            <label for="a4">Not really</label>
                        </div>
                    </div>
                    <span class="field-error" style="display:none;"></span>
                </div>

                <div class="form-group" data-group="three_words">
                    <label for="three_words">💙 Describe your experience in 3 words<span class="required-mark">*</span></label>
                    <input type="text" id="three_words" name="three_words" maxlength="150" placeholder="Example: Fun, Faithful, Memorable">
                    <span class="field-error" style="display:none;"></span>
                </div>
            </div>

            <!-- ============ SECTION: YOUR THOUGHTS ============ -->
            <div class="form-section" data-section="thoughts" style="margin-top:36px;">
                <div class="section-heading"><span class="icon">💡</span> Your Thoughts</div>
                <p class="section-sub">Help us plan an even better next event</p>

                <div class="form-group" data-group="improvement">
                    <label for="improvement">What would you like us to improve next time?<span class="required-mark">*</span></label>
                    <textarea id="improvement" name="improvement" maxlength="2000" placeholder="We'd love to hear your honest thoughts"></textarea>
                    <span class="field-error" style="display:none;"></span>
                </div>

                <div class="form-group" data-group="next_event">
                    <label for="next_event">What would you love to see in our next Altar Server activity?</label>
                    <textarea id="next_event" name="next_event" maxlength="2000" placeholder="Optional"></textarea>
                    <span class="hint">Optional</span>
                </div>

                <div class="form-group" data-group="message_to_organizers">
                    <label for="message_to_organizers">Leave a message for the organizers or fellow altar servers</label>
                    <textarea id="message_to_organizers" name="message_to_organizers" maxlength="2000" placeholder="Optional"></textarea>
                    <span class="hint">Optional</span>
                </div>
            </div>

            <!-- ============ SECTION: FINAL THOUGHT ============ -->
            <div class="form-section" data-section="final" style="margin-top:36px;">
                <div class="section-heading"><span class="icon">💙</span> Final Thought</div>
                <p class="section-sub">Just one more question!</p>

                <div class="form-group" data-group="join_again">
                    <label>Would you join another Altar Server event like this?<span class="required-mark">*</span></label>
                    <div class="choice-group">
                        <div class="choice-option">
                            <input type="radio" id="j1" name="join_again" value="YES!">
                            <label for="j1">YES! 🙌</label>
                        </div>
                        <div class="choice-option">
                            <input type="radio" id="j2" name="join_again" value="Definitely! See you next time!">
                            <label for="j2">Definitely! See you next time! ❤️</label>
                        </div>
                        <div class="choice-option">
                            <input type="radio" id="j3" name="join_again" value="Maybe">
                            <label for="j3">Maybe</label>
                        </div>
                        <div class="choice-option">
                            <input type="radio" id="j4" name="join_again" value="I'm not sure">
                            <label for="j4">I'm not sure</label>
                        </div>
                    </div>
                    <span class="field-error" style="display:none;"></span>
                </div>
            </div>

            <button type="submit" id="submit-btn" class="btn btn-primary btn-block" style="margin-top:30px;">
                <span class="spinner"></span>
                <span class="btn-label">SUBMIT MY FEEDBACK 💙</span>
            </button>
        </form>

        <footer class="site-footer">
            <div class="footer-title">ALTAR SERVER MONTH 2026</div>
            <div class="footer-theme">&ldquo;Serving with Faith, Serving with Joy&rdquo;</div>
            <div>St. Peregrine Quasi-Parish</div>
            <div>All Vicariates under the Cathedral of St. Augustine</div>
            <div class="footer-verse">&ldquo;Serve the Lord with gladness.&rdquo; &mdash; Psalm 100:2</div>
        </footer>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
