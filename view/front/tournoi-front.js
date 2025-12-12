const API_URL = 'tournoi_handler.php';

document.addEventListener('DOMContentLoaded', loadTournois);

async function loadTournois() {
  try {
    const response = await fetch(API_URL);
    const result = await response.json();
    const container = document.getElementById('tournoiList');
    
    if (result.success && result.data.length > 0) {
      container.innerHTML = result.data.map(t => {
        const status = getStatus(t.date_debut, t.date_fin);
        const badge = getBadge(status);
        const btnClass = status === 'soon' ? 'btn-disabled' : 'btn-primary-custom';
        const btnText = status === 'soon' ? 'Coming Soon' : 'Join Now';
        const btnDisabled = status === 'soon' ? 'disabled' : '';
        
        // Image HTML with fallback
        const imageHtml = t.image 
          ? `<img src="${escapeHtml(t.image)}" alt="${escapeHtml(t.nom)}" class="tournament-image">`
          : `<div class="tournament-image-placeholder">🏆</div>`;
        
        return `
          <div class="card">
            <div class="card-body">
              ${imageHtml}
              <h5 class="card-title">${escapeHtml(t.nom)}</h5>
              <p class="card-text">
                <strong>Theme:</strong> ${escapeHtml(t.theme)}<br>
                <strong>Level:</strong> ${getNiveauBadge(t.niveau)}<br>
                <strong>Date:</strong> ${formatDateRange(t.date_debut, t.date_fin)}
              </p>

              <div class="card-footer">
                <div class="card-info">
                  ${badge}
                  <small>${getInfo(status, t.id)}</small>
                </div>
                <button class="btn ${btnClass}" ${btnDisabled}>${btnText}</button>
              </div>
            </div>
          </div>
        `;
      }).join('');
    } else {
      container.innerHTML = '<p style="color: white; text-align: center;">No tournaments available</p>';
    }
  } catch (error) {
    console.error('Error:', error);
    document.getElementById('tournoiList').innerHTML = 
      '<p style="color: white; text-align: center;">Error loading tournaments</p>';
  }
}

function getStatus(dateDebut, dateFin) {
  const now = new Date();
  const start = new Date(dateDebut);
  const end = new Date(dateFin);
  if (now < start) return 'soon';
  if (now >= start && now <= end) return 'running';
  return 'open';
}

function getBadge(status) {
  const badges = {
    'open': '<span class="badge-open">Open</span>',
    'soon': '<span class="badge-soon">Coming Soon</span>',
    'running': '<span class="badge-running">In Progress</span>'
  };
  return badges[status] || badges['open'];
}

function getNiveauBadge(niveau) {
  const badges = {
    'Débutant': '<span style="background: #d4f4dd; color: #20c997; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">🟢 Beginner</span>',
    'Intermédiaire': '<span style="background: #fff3cd; color: #ffa500; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">🟡 Intermediate</span>',
    'Expert': '<span style="background: #f8d7da; color: #dc3545; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">🔴 Expert</span>'
  };
  return badges[niveau] || escapeHtml(niveau);
}

function getInfo(status, id) {
  const infos = {
    'open': `Participants: ${Math.floor(Math.random() * 50) + 20}`,
    'soon': `Pre-registrations: ${Math.floor(Math.random() * 15) + 5}`,
    'running': `Top score: ${Math.floor(Math.random() * 500) + 500} pts`
  };
  return infos[status] || infos['open'];
}

function formatDateRange(start, end) {
  const options = { month: '2-digit', day: '2-digit', year: 'numeric' };
  const startFormatted = new Date(start).toLocaleDateString('en-US', options);
  const endFormatted = new Date(end).toLocaleDateString('en-US', options);
  return `${startFormatted} → ${endFormatted}`;
}

function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}