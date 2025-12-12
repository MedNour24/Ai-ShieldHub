# 🎮 Guide d'intégration rapide : Système de Gamification

## 📦 Fichiers créés

✅ `rewards.js` - Logique JavaScript pour les récompenses  
✅ `rewards.css` - Styles CSS pour l'affichage  
✅ Ce guide d'intégration

---

## 🚀 Étape 1 : Inclure les fichiers dans `tour.html`

### Dans la section `<head>`, après `tour.css` :

```html
<link rel="stylesheet" href="tour.css">
<link rel="stylesheet" href="rewards.css">  <!-- AJOUTER CETTE LIGNE -->
```

### Avant la fermeture `</body>`, après le script Bootstrap :

```html
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="rewards.js"></script>  <!-- AJOUTER CETTE LIGNE -->
<script>
  // Votre code JavaScript existant...
```

---

## 🎯 Étape 2 : Modifier la fonction `displayTournaments`

### TROUVER cette section (vers la ligne 630) :

```javascript
              <div class="card-footer">
                <div class="card-info">
                  ${badge}
                  <small><i class="fas fa-users me-1"></i>${getInfo(status, t.id)}</small>
                </div>
                <button class="btn ${btnClass}" ${btnDisabled} onclick="joinTournament(${t.id})">
                  <i class="fas fa-sign-in-alt me-2"></i>${btnText}
                </button>
              </div>
            </div>
          </div>
```

### REMPLACER PAR :

```javascript
              <div class="card-footer">
                <div class="card-info">
                  ${badge}
                  <small><i class="fas fa-users me-1"></i>${getInfo(status, t.id)}</small>
                </div>
                <button class="btn ${btnClass}" ${btnDisabled} onclick="joinTournament(${t.id})">
                  <i class="fas fa-sign-in-alt me-2"></i>${btnText}
                </button>
              </div>
              
              ${generateRewardsPreview(t)}
            </div>
          </div>
```

**Note :** Ajoutez simplement `${generateRewardsPreview(t)}` après le `card-footer` et avant les deux `</div>` de fermeture.

---

## 🔄 Étape 3 : Initialiser les tooltips après le rendu

### À LA FIN de la fonction `displayTournaments`, AJOUTER :

```javascript
function displayTournaments(tournaments) {
    const container = document.getElementById('tournoiList');
    
    container.innerHTML = tournaments.map(t => {
        // ... tout le code existant ...
    }).join('');
    
    // AJOUTER CES LIGNES :
    // Initialize tooltips after rendering cards
    setTimeout(initializeTooltips, 100);
}
```

---

## ✅ Vérification

Après ces 3 étapes, vous devriez voir :

1. ✨ Une section dorée en bas de chaque carte de tournoi
2. 🎁 Une icône cadeau animée à gauche
3. 🏆 Trois icônes (Trophée, Médaille, Certificat) qui flottent
4. 💡 Des tooltips au survol avec les détails des récompenses

---

## 🎨 Personnalisation

### Modifier les points XP

Dans `rewards.js`, ligne 18-23 :

```javascript
const pointsMap = {
    'Débutant': 750,      // Changer ici
    'Intermédiaire': 1500,
    'Expert': 3000
};
```

### Modifier les noms de badges

Dans `rewards.js`, ligne 33-38 :

```javascript
const badgeMap = {
    'Débutant': 'Bronze Shield',
    'Intermédiaire': 'Silver Shield',
    'Expert': 'Gold Shield'
};
```

### Modifier les couleurs

Dans `rewards.css`, ligne 14-17 :

```css
background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 140, 0, 0.1));
border: 1px solid rgba(255, 215, 0, 0.3);
```

---

## 🐛 Dépannage

### Les tooltips ne s'affichent pas
- Vérifiez que Bootstrap 5.1.3 est chargé
- Vérifiez que `rewards.js` est chargé APRÈS Bootstrap
- Ouvrez la console (F12) et cherchez des erreurs

### Les icônes ne flottent pas
- Vérifiez que `rewards.css` est bien chargé
- Vérifiez qu'il n'y a pas de conflits CSS

### La section ne s'affiche pas
- Vérifiez que `${generateRewardsPreview(t)}` est bien ajouté
- Vérifiez que la fonction `escapeHtml` existe dans votre code

---

## 📝 Résumé des modifications

**Fichiers à modifier :** `tour.html` uniquement  
**Lignes à ajouter :** 3 lignes (2 includes + 1 appel de fonction + 1 initialisation)  
**Temps estimé :** 5 minutes

---

**C'est tout ! Votre système de gamification est prêt ! 🎉**
