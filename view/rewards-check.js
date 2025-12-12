/**
 * ============================================
 * AUTOMATIC INTEGRATION HELPER
 * ============================================
 * This script helps verify that the rewards system
 * is properly integrated into your application.
 */

(function () {
    'use strict';

    console.log('🎮 Rewards System Integration Checker');
    console.log('=====================================');

    const checks = {
        bootstrap: false,
        rewardsCSS: false,
        rewardsJS: false,
        escapeHtml: false,
        generateRewardsPreview: false,
        initializeTooltips: false
    };

    // Check Bootstrap
    if (typeof bootstrap !== 'undefined') {
        checks.bootstrap = true;
        console.log('✅ Bootstrap is loaded');
    } else {
        console.error('❌ Bootstrap is NOT loaded - Tooltips will not work!');
        console.log('   Add: <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>');
    }

    // Check rewards.css
    const stylesheets = Array.from(document.styleSheets);
    const rewardsCSSLoaded = stylesheets.some(sheet => {
        try {
            return sheet.href && sheet.href.includes('rewards.css');
        } catch (e) {
            return false;
        }
    });

    if (rewardsCSSLoaded) {
        checks.rewardsCSS = true;
        console.log('✅ rewards.css is loaded');
    } else {
        console.error('❌ rewards.css is NOT loaded - Styles will not work!');
        console.log('   Add: <link rel="stylesheet" href="rewards.css">');
    }

    // Check rewards.js functions
    if (typeof getRewardPoints === 'function') {
        checks.rewardsJS = true;
        console.log('✅ rewards.js is loaded (getRewardPoints found)');
    } else {
        console.error('❌ rewards.js is NOT loaded!');
        console.log('   Add: <script src="rewards.js"></script>');
    }

    if (typeof generateRewardsPreview === 'function') {
        checks.generateRewardsPreview = true;
        console.log('✅ generateRewardsPreview function is available');
    } else {
        console.error('❌ generateRewardsPreview function is NOT available!');
    }

    if (typeof initializeTooltips === 'function') {
        checks.initializeTooltips = true;
        console.log('✅ initializeTooltips function is available');
    } else {
        console.error('❌ initializeTooltips function is NOT available!');
    }

    // Check escapeHtml function
    if (typeof escapeHtml === 'function') {
        checks.escapeHtml = true;
        console.log('✅ escapeHtml function is available');
    } else {
        console.warn('⚠️  escapeHtml function is NOT available - Add it to your code!');
        console.log('   function escapeHtml(text) {');
        console.log('       if (!text) return \'\';');
        console.log('       const div = document.createElement(\'div\');');
        console.log('       div.textContent = text;');
        console.log('       return div.innerHTML;');
        console.log('   }');
    }

    // Summary
    console.log('');
    console.log('📊 Integration Summary');
    console.log('=====================');

    const totalChecks = Object.keys(checks).length;
    const passedChecks = Object.values(checks).filter(v => v).length;
    const percentage = Math.round((passedChecks / totalChecks) * 100);

    console.log(`Status: ${passedChecks}/${totalChecks} checks passed (${percentage}%)`);

    if (percentage === 100) {
        console.log('');
        console.log('🎉 PERFECT! Everything is properly integrated!');
        console.log('');
        console.log('Next steps:');
        console.log('1. Add ${generateRewardsPreview(tournament)} to your displayTournaments function');
        console.log('2. Call initializeTooltips() after rendering cards');
        console.log('3. Test by hovering over reward icons');
    } else if (percentage >= 75) {
        console.log('');
        console.log('⚠️  Almost there! Fix the issues above to complete integration.');
    } else {
        console.log('');
        console.log('❌ Integration incomplete. Please follow the REWARDS_INTEGRATION_GUIDE.md');
    }

    console.log('');
    console.log('📖 For detailed instructions, see: REWARDS_INTEGRATION_GUIDE.md');
    console.log('🎮 For a working demo, open: rewards-demo.html');
    console.log('');

    // Test reward calculation if available
    if (checks.rewardsJS) {
        console.log('🧪 Testing reward calculations:');
        console.log('   Débutant: ' + getRewardPoints('Débutant') + ' XP → ' + getRewardBadge('Débutant'));
        console.log('   Intermédiaire: ' + getRewardPoints('Intermédiaire') + ' XP → ' + getRewardBadge('Intermédiaire'));
        console.log('   Expert: ' + getRewardPoints('Expert') + ' XP → ' + getRewardBadge('Expert'));
        console.log('');
    }

})();
