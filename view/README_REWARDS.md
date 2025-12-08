# 🎮 Système de Gamification - Fichiers Modulaires

## 📦 Fichiers créés

| Fichier | Type | Description |
|---------|------|-------------|
| `rewards.js` | JavaScript | Logique de calcul des récompenses et tooltips |
| `rewards.css` | CSS | Styles, animations et responsive design |
| `rewards-demo.html` | HTML | Page de démonstration du système |
| `REWARDS_INTEGRATION_GUIDE.md` | Documentation | Guide d'intégration étape par étape |
| `README_REWARDS.md` | Documentation | Ce fichier |

---

## 🚀 Démarrage rapide

### 1. Tester la démo

Ouvrez `rewards-demo.html` dans votre navigateur pour voir le système en action :

```
http://localhost/Aishieldhub/View/rewards-demo.html
```

### 2. Intégrer dans `tour.html`

Suivez le guide `REWARDS_INTEGRATION_GUIDE.md` (3 étapes simples, 5 minutes)

---

## ✨ Fonctionnalités

### 🏆 Récompenses dynamiques

- **Points XP** : Calculés selon la difficulté
  - Débutant : 500 XP
  - Intermédiaire : 1000 XP
  - Expert : 2000 XP

- **Badges** : Noms personnalisés par niveau
  - Débutant : "Cyber Novice"
  - Intermédiaire : "Cyber Warrior"
  - Expert : "Cyber Master"

- **Certificats** : Certificat officiel pour tous

### 🎨 Design

- ✨ Animations de flottement sur les icônes
- 💫 Effet de pulsation sur l'icône cadeau
- 🎁 Fond doré avec effet glassmorphism
- 💡 Tooltips Bootstrap personnalisés
- 📱 Design 100% responsive

### 🔧 Architecture

```
rewards.js
├── getRewardPoints(niveau)      → Calcule les XP
├── getRewardBadge(niveau)       → Retourne le nom du badge
├── generateRewardsPreview(t)    → Génère le HTML
└── initializeTooltips()         → Active les tooltips Bootstrap

rewards.css
├── .rewards-preview             → Container principal
├── .reward-item                 → Chaque récompense
├── .reward-icon                 → Icônes animées
├── @keyframes float             → Animation flottement
└── @keyframes pulse             → Animation pulsation
```

---

## 📝 Utilisation

### Dans votre code JavaScript

```javascript
// Générer la section récompenses pour un tournoi
const tournament = {
    id: 1,
    nom: "Web Security Challenge",
    niveau: "Intermédiaire"
};

const rewardsHTML = generateRewardsPreview(tournament);
// Retourne le HTML de la section récompenses

// Initialiser les tooltips après le rendu
initializeTooltips();
```

### Dans votre HTML

```html
<!-- Inclure les fichiers -->
<link rel="stylesheet" href="rewards.css">
<script src="rewards.js"></script>

<!-- La section sera générée automatiquement -->
<div class="card">
    <!-- Contenu de la carte -->
    ${generateRewardsPreview(tournament)}
</div>
```

---

## 🎨 Personnalisation

### Modifier les récompenses

**Fichier :** `rewards.js`

```javascript
// Changer les points XP (ligne 18-23)
const pointsMap = {
    'Débutant': 750,        // Nouveau montant
    'Intermédiaire': 1500,
    'Expert': 3000
};

// Changer les noms de badges (ligne 33-38)
const badgeMap = {
    'Débutant': 'Bronze Shield',
    'Intermédiaire': 'Silver Shield',
    'Expert': 'Gold Shield'
};
```

### Modifier les couleurs

**Fichier :** `rewards.css`

```css
/* Changer le fond de la section (ligne 14-17) */
.rewards-preview {
    background: linear-gradient(135deg, rgba(0, 188, 212, 0.1), rgba(0, 150, 199, 0.1));
    border: 1px solid rgba(0, 188, 212, 0.3);
}

/* Changer la couleur de la valeur dans le tooltip (ligne 99-103) */
.reward-value {
    color: #00bcd4;  /* Cyan au lieu de doré */
}
```

### Modifier les icônes

**Fichier :** `rewards.js` (ligne 63-85)

```javascript
// Remplacer les emojis par des icônes Font Awesome
<div class="reward-icon"><i class="fas fa-trophy"></i></div>
<div class="reward-icon"><i class="fas fa-medal"></i></div>
<div class="reward-icon"><i class="fas fa-certificate"></i></div>
```

---

## 🔍 Dépannage

### Les tooltips ne s'affichent pas

**Cause :** Bootstrap n'est pas chargé ou chargé après `rewards.js`

**Solution :**
```html
<!-- Bootstrap DOIT être chargé AVANT rewards.js -->
<script src="bootstrap.bundle.min.js"></script>
<script src="rewards.js"></script>
```

### Les animations ne fonctionnent pas

**Cause :** `rewards.css` n'est pas chargé

**Solution :**
```html
<!-- Vérifier que le fichier est bien inclus -->
<link rel="stylesheet" href="rewards.css">
```

### La fonction `generateRewardsPreview` n'est pas définie

**Cause :** `rewards.js` n'est pas chargé

**Solution :**
```html
<!-- Vérifier le chemin du fichier -->
<script src="rewards.js"></script>
```

### Erreur "escapeHtml is not defined"

**Cause :** La fonction `escapeHtml` n'existe pas dans votre code

**Solution :** Ajouter cette fonction dans votre JavaScript :
```javascript
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
```

---

## 📊 Compatibilité

- ✅ Bootstrap 5.1.3+
- ✅ Font Awesome 6.1.1+
- ✅ Navigateurs modernes (Chrome, Firefox, Safari, Edge)
- ✅ Mobile et tablette
- ✅ IE11+ (avec polyfills)

---

## 🎯 Prochaines étapes

1. ✅ Tester la démo (`rewards-demo.html`)
2. ✅ Lire le guide d'intégration
3. ✅ Intégrer dans `tour.html`
4. ✅ Personnaliser selon vos besoins
5. ✅ Profiter ! 🎉

---

## 📞 Support

Si vous rencontrez des problèmes :

1. Vérifiez que tous les fichiers sont bien chargés (Console F12)
2. Consultez le guide d'intégration
3. Vérifiez les exemples dans `rewards-demo.html`
4. Assurez-vous que Bootstrap est chargé

---

**Créé avec ❤️ pour AI ShieldHub**  
**Version :** 1.0.0  
**Date :** 2025-12-01
