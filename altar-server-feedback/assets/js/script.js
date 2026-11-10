/**
 * assets/js/script.js
 * ---------------------------------------------------------------------
 * Handles FRONTEND interactivity and validation for feedback.php & dashboard live search
 * ---------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('feedback-form');
    
    /* -----------------------------------------------------------
       1–4: Feedback Form Handling (Runs if feedback-form exists)
       ----------------------------------------------------------- */
    if (form) {
        const submitBtn = document.getElementById('submit-btn');
        const submitLabel = submitBtn.querySelector('.btn-label');
        const originalLabel = submitLabel.textContent;
        const formMessage = document.getElementById('form-message');

        // Star rating setup
        const starButtons = Array.from(document.querySelectorAll('.star-btn'));
        const ratingInput = document.getElementById('overall_rating');
        const starFeedbackText = document.getElementById('star-feedback-text');

        const ratingCopy = {
            1: { stars: '⭐', label: 'Not satisfied', msg: "Thanks for your honesty — we'll do better next time." },
            2: { stars: '⭐⭐', label: 'Needs improvement', msg: 'We hear you — your feedback helps us improve.' },
            3: { stars: '⭐⭐⭐', label: 'Average', msg: "Thanks for sharing — we'd love to hear how to make it even better." },
            4: { stars: '⭐⭐⭐⭐', label: 'Good', msg: "Glad you had a good time! We're always aiming higher." },
            5: { stars: '⭐⭐⭐⭐⭐', label: 'Excellent', msg: "Excellent! We're so glad you enjoyed it! 💙" }
        };

        function paintStars(value, isPreview) {
            starButtons.forEach(function (btn) {
                const btnValue = parseInt(btn.getAttribute('data-value'), 10);
                btn.classList.remove('is-filled', 'is-hover');
                if (btnValue <= value) {
                    btn.classList.add(isPreview ? 'is-hover' : 'is-filled');
                    btn.textContent = '★';
                } else {
                    btn.textContent = '☆';
                }
            });
        }

        function setRating(value) {
            ratingInput.value = value;
            paintStars(value, false);
            const copy = ratingCopy[value];
            starFeedbackText.innerHTML = copy.stars + '<span class="rating-msg">' + copy.label + ' — ' + copy.msg + '</span>';
            clearFieldError('overall_rating');
        }

        starButtons.forEach(function (btn) {
            const value = parseInt(btn.getAttribute('data-value'), 10);

            btn.addEventListener('click', function () {
                setRating(value);
            });

            btn.addEventListener('mouseenter', function () {
                if (!ratingInput.value) paintStars(value, true);
            });

            btn.addEventListener('mouseleave', function () {
                paintStars(parseInt(ratingInput.value, 10) || 0, false);
            });

            btn.addEventListener('focus', function () {
                if (!ratingInput.value) paintStars(value, true);
            });
        });

        // Progress bar setup
        const sections = Array.from(document.querySelectorAll('.form-section'));
        const progressFill = document.getElementById('progress-fill');
        const progressStepLabel = document.getElementById('progress-step-label');
        const progressStepCount = document.getElementById('progress-step-count');

        const sectionNames = {
            about: 'About You',
            experience: 'Your Experience',
            faith: 'Faith & Service',
            thoughts: 'Your Thoughts',
            final: 'Final Thought'
        };

        function updateProgress() {
            if (!sections.length) return;
            const viewportMiddle = window.scrollY + (window.innerHeight * 0.35);
            let activeIndex = 0;

            sections.forEach(function (section, index) {
                if (section.offsetTop <= viewportMiddle) {
                    activeIndex = index;
                }
            });

            const activeSection = sections[activeIndex];
            const key = activeSection.getAttribute('data-section');
            progressStepLabel.textContent = sectionNames[key] || 'Feedback Form';
            progressStepCount.textContent = 'Step ' + (activeIndex + 1) + ' of ' + sections.length;

            const pct = Math.round(((activeIndex + 1) / sections.length) * 100);
            progressFill.style.width = pct + '%';
        }

        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();

        // Form Validation helpers
        const requiredFields = [
            'parish_vicariate', 'overall_rating', 'enjoyed_most', 'favorite_activity',
            'feeling', 'met_other_servers', 'learned_realized', 'appreciated_service',
            'three_words', 'improvement', 'join_again'
        ];

        const friendlyMessages = {
            parish_vicariate: 'Please tell us your parish or vicariate. 💙',
            overall_rating: 'Please choose a rating by tapping a star.',
            enjoyed_most: 'Please tell us what made the event memorable. 💙',
            favorite_activity: 'Please share what you enjoyed most. 💙',
            feeling: 'Please choose how you felt during the event.',
            met_other_servers: 'Please answer this question.',
            learned_realized: 'Please share something you learned or realized. 💙',
            appreciated_service: 'Please answer this question.',
            three_words: 'Please describe your experience in three words.',
            improvement: 'Please share a suggestion for improvement. 💙',
            join_again: 'Please answer this question.'
        };

        function showFieldError(name, message) {
            const group = form.querySelector('[data-group="' + name + '"]');
            if (!group) return;
            group.classList.add('has-error');
            const errorEl = group.querySelector('.field-error');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.style.display = 'flex';
            }
        }

        function clearFieldError(name) {
            const group = form.querySelector('[data-group="' + name + '"]');
            if (!group) return;
            group.classList.remove('has-error');
            const errorEl = group.querySelector('.field-error');
            if (errorEl) errorEl.style.display = 'none';
        }

        function clearAllErrors() {
            form.querySelectorAll('.form-group').forEach(function (g) { g.classList.remove('has-error'); });
            form.querySelectorAll('.field-error').forEach(function (e) { e.style.display = 'none'; });
            formMessage.style.display = 'none';
        }

        function isChecked(name) {
            return !!form.querySelector('input[name="' + name + '"]:checked');
        }

        function isFilled(name) {
            const field = form.querySelector('[name="' + name + '"]');
            return field && field.value.trim().length > 0;
        }

        function validateForm() {
            clearAllErrors();
            let firstInvalidGroup = null;
            let valid = true;

            requiredFields.forEach(function (name) {
                let fieldValid;
                if (name === 'overall_rating') {
                    fieldValid = ratingInput.value.trim().length > 0;
                } else if (form.querySelectorAll('input[name="' + name + '"]').length > 1) {
                    fieldValid = isChecked(name);
                } else {
                    fieldValid = isFilled(name);
                }

                if (!fieldValid) {
                    valid = false;
                    showFieldError(name, friendlyMessages[name] || 'This field is required.');
                    if (!firstInvalidGroup) {
                        firstInvalidGroup = form.querySelector('[data-group="' + name + '"]');
                    }
                } else {
                    clearFieldError(name);
                }
            });

            if (!valid && firstInvalidGroup) {
                firstInvalidGroup.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            return valid;
        }

        // Submit listener
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!validateForm()) {
                formMessage.className = 'alert alert-error';
                formMessage.textContent = 'Please complete the highlighted fields before submitting. 🙏';
                formMessage.style.display = 'flex';
                return;
            }

            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
            submitLabel.textContent = 'Submitting...';

            const formData = new FormData(form);

            fetch('submit_feedback.php', {
                method: 'POST',
                body: formData
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.success) {
                        window.location.href = 'success.php';
                    } else {
                        formMessage.className = 'alert alert-error';
                        formMessage.textContent = data.message || 'Something went wrong. Please check the form and try again.';
                        formMessage.style.display = 'flex';

                        if (data.errors) {
                            Object.keys(data.errors).forEach(function (name) {
                                showFieldError(name, data.errors[name]);
                            });
                        }

                        submitBtn.disabled = false;
                        submitBtn.classList.remove('is-loading');
                        submitLabel.textContent = originalLabel;
                        formMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                })
                .catch(function () {
                    formMessage.className = 'alert alert-error';
                    formMessage.textContent = 'Something went wrong while submitting your feedback. Please try again.';
                    formMessage.style.display = 'flex';
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('is-loading');
                    submitLabel.textContent = originalLabel;
                });
        });
    }

    /* -----------------------------------------------------------
       5. Automatic Live Search Filter (admin/dashboard.php)
       ----------------------------------------------------------- */
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            // Filter table rows
            const tableRows = document.querySelectorAll('.feedback-table tbody tr');
            tableRows.forEach(function (row) {
                const nameText = row.children[1] ? row.children[1].textContent.toLowerCase() : '';
                const parishText = row.children[2] ? row.children[2].textContent.toLowerCase() : '';

                if (nameText.includes(query) || parishText.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // Filter mobile cards
            const cards = document.querySelectorAll('.feedback-cards .fb-card');
            cards.forEach(function (card) {
                const nameEl = card.querySelector('.fb-name');
                const parishEl = card.querySelector('.fb-parish');

                const nameText = nameEl ? nameEl.textContent.toLowerCase() : '';
                const parishText = parishEl ? parishEl.textContent.toLowerCase() : '';

                if (nameText.includes(query) || parishText.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});