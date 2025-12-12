/**
 * ============================================
 * GAMIFICATION REWARDS SYSTEM
 * ============================================
 * This module handles the rewards preview functionality
 * for tournament cards, including tooltips and animations.
 */

// ============================================
// REWARD CALCULATION FUNCTIONS
// ============================================

/**
 * Get reward points based on tournament difficulty level
 * @param {string} niveau - Tournament difficulty level
 * @returns {number} - XP points to be awarded
 */
function getRewardPoints(niveau) {
    const pointsMap = {
        'Débutant': 500,
        'Intermédiaire': 1000,
        'Expert': 2000
    };
    return pointsMap[niveau] || 500;
}

/**
 * Get reward badge name based on tournament difficulty level
 * @param {string} niveau - Tournament difficulty level
 * @returns {string} - Badge name
 */
function getRewardBadge(niveau) {
    const badgeMap = {
        'Débutant': 'Cyber Novice',
        'Intermédiaire': 'Cyber Warrior',
        'Expert': 'Cyber Master'
    };
    return badgeMap[niveau] || 'Cyber Champion';
}

// ============================================
// TOOLTIP INITIALIZATION
// ============================================

/**
 * Initialize Bootstrap tooltips for reward items
 * This function should be called after tournament cards are rendered
 */
function initializeTooltips() {
    // Dispose of existing tooltips to prevent duplicates
    const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    existingTooltips.forEach(el => {
        const tooltip = bootstrap.Tooltip.getInstance(el);
        if (tooltip) {
            tooltip.dispose();
        }
    });

    // Initialize new tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true,
            trigger: 'hover',
            placement: 'top',
            customClass: 'reward-tooltip'
        });
    });
}

/**
 * Generate rewards preview HTML for a tournament card
 * @param {Object} tournament - Tournament object with id, nom, niveau
 * @returns {string} - HTML string for rewards preview section
 */
function generateRewardsPreview(tournament) {
    const points = getRewardPoints(tournament.niveau);
    const badge = getRewardBadge(tournament.niveau);
    const tournamentName = escapeHtml(tournament.nom);

    return `
        <!-- Rewards Preview Section -->
        <div class="rewards-preview">
            <div class="reward-item" 
                 data-bs-toggle="tooltip" 
                 data-bs-placement="top" 
                 data-bs-html="true"
                 title="<div class='reward-value'>+${points} XP</div><div class='reward-description'>Experience points for completing this tournament</div>">
                <div class="reward-icon">🏆</div>
                <div class="reward-label">Points</div>
            </div>
            
            <div class="reward-item" 
                 data-bs-toggle="tooltip" 
                 data-bs-placement="top" 
                 data-bs-html="true"
                 title="<div class='reward-value'>${badge}</div><div class='reward-description'>Exclusive badge for ${tournamentName}</div>">
                <div class="reward-icon">🎖️</div>
                <div class="reward-label">Badge</div>
            </div>
            
            <div class="reward-item" 
                 data-bs-toggle="tooltip" 
                 data-bs-placement="top" 
                 data-bs-html="true"
                 title="<div class='reward-value'>Certificate</div><div class='reward-description'>Official completion certificate</div>">
                <div class="reward-icon">📜</div>
                <div class="reward-label">Certificate</div>
            </div>
        </div>
    `;
}

// ============================================
// AUTO-INITIALIZATION
// ============================================

// Re-initialize tooltips when DOM content changes
if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                // Check if any added nodes contain reward items
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && (node.classList?.contains('card') || node.querySelector?.('.rewards-preview'))) {
                        setTimeout(initializeTooltips, 100);
                    }
                });
            }
        });
    });

    // Observe the tournament list container
    document.addEventListener('DOMContentLoaded', function() {
        const tournoiList = document.getElementById('tournoiList');
        if (tournoiList) {
            observer.observe(tournoiList, {
                childList: true,
                subtree: true
            });
        }
    });
}

console.log('✅ Gamification Rewards System loaded successfully');
