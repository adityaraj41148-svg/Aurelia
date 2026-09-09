/**
 * Interactive Digital Magazine Flipbook Controller
 * Onam Edit - AA Mart / AJIO Fashionation
 */

document.addEventListener('DOMContentLoaded', () => {
    let currentPage = 1;
    const totalPages = 6;
    let isAnimating = false;

    // DOM Elements
    const pages = document.querySelectorAll('.magazine-page');
    const btnPrev = document.getElementById('btn-page-prev');
    const btnNext = document.getElementById('btn-page-next');
    const pageCounter = document.getElementById('page-counter-text');
    const quickTabs = document.querySelectorAll('.quick-tab-btn');
    const magazineViewport = document.getElementById('magazine-viewport');

    if (!pages.length) return;

    let audioCtx = null;
    function getAudioContext() {
        if (!audioCtx) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) audioCtx = new AudioContextClass();
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    function playTurnSound() {
        try {
            const ctx = getAudioContext();
            if (!ctx) return;
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(320, now);
            osc.frequency.exponentialRampToValueAtTime(1100, now + 0.07);

            gain.gain.setValueAtTime(0.05, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.07);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(now);
            osc.stop(now + 0.07);
        } catch (e) {}
    }

    function updatePage(targetPage, direction = 'next') {
        if (targetPage < 1 || targetPage > totalPages || targetPage === currentPage || isAnimating) return;
        
        isAnimating = true;
        const oldPageNum = currentPage;
        currentPage = targetPage;

        playTurnSound();

        const oldPageEl = document.querySelector(`.magazine-page[data-page="${oldPageNum}"]`);
        const newPageEl = document.querySelector(`.magazine-page[data-page="${currentPage}"]`);

        pages.forEach((page) => page.classList.remove('flipping-next', 'flipping-prev'));

        if (direction === 'next' && oldPageEl) {
            oldPageEl.classList.add('flipping-next');
        } else if (direction === 'prev' && newPageEl) {
            newPageEl.classList.add('flipping-prev');
        }

        setTimeout(() => {
            pages.forEach((page, idx) => {
                const pageNum = idx + 1;
                page.classList.remove('page-active', 'page-hidden-right', 'page-flipped-left', 'flipping-next', 'flipping-prev');

                if (pageNum < currentPage) {
                    page.classList.add('page-flipped-left');
                } else if (pageNum === currentPage) {
                    page.classList.add('page-active');
                } else {
                    page.classList.add('page-hidden-right');
                }
            });

            if (btnPrev) btnPrev.disabled = (currentPage === 1);
            if (btnNext) btnNext.disabled = (currentPage === totalPages);

            if (pageCounter) {
                pageCounter.textContent = `Page ${currentPage} of ${totalPages}`;
            }

            quickTabs.forEach((tab) => {
                const tabNum = parseInt(tab.dataset.page, 10);
                if (tabNum === currentPage) tab.classList.add('active');
                else tab.classList.remove('active');
            });

            isAnimating = false;
        }, 600);
    }

    function nextPage() {
        if (currentPage < totalPages) updatePage(currentPage + 1, 'next');
    }

    function previousPage() {
        if (currentPage > 1) updatePage(currentPage - 1, 'prev');
    }

    window.goToPage = function(pageNum) {
        if (pageNum === currentPage) return;
        const dir = pageNum > currentPage ? 'next' : 'prev';
        updatePage(pageNum, dir);
    };

    if (btnPrev) {
        btnPrev.addEventListener('click', (e) => {
            e.preventDefault();
            previousPage();
        });
    }

    if (btnNext) {
        btnNext.addEventListener('click', (e) => {
            e.preventDefault();
            nextPage();
        });
    }

    quickTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const targetPageNum = parseInt(tab.dataset.page, 10);
            goToPage(targetPageNum);
        });
    });

    document.addEventListener('keydown', (e) => {
        const activeTag = document.activeElement ? document.activeElement.tagName : '';
        const isEditable = document.activeElement ? document.activeElement.isContentEditable : false;
        if (activeTag === 'INPUT' || activeTag === 'TEXTAREA' || activeTag === 'SELECT' || isEditable) return;

        if (e.key === 'ArrowRight') nextPage();
        else if (e.key === 'ArrowLeft') previousPage();
    });

    let touchStartX = 0, touchStartY = 0;
    if (magazineViewport) {
        magazineViewport.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
            }
        }, { passive: true });

        magazineViewport.addEventListener('touchend', (e) => {
            if (e.changedTouches.length === 1) {
                const deltaX = e.changedTouches[0].clientX - touchStartX;
                const deltaY = e.changedTouches[0].clientY - touchStartY;
                if (Math.abs(deltaX) > 40 && Math.abs(deltaX) > Math.abs(deltaY)) {
                    if (deltaX < 0) nextPage();
                    else previousPage();
                }
            }
        }, { passive: true });
    }

    // Initialize Page 1
    updatePage(1);
});
