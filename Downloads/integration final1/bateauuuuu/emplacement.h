#ifndef EMPLACEMENT_H
#define EMPLACEMENT_H

#include <QString>
#include <QDate>
#include <QTableWidget>
#include <QComboBox>
#include <QWidget>
#include <QLabel>
#include <QLayout>
#include <QLayoutItem>
#include <functional>  //(passer une fonction en paramètre)

class emplacement
{
public:
    emplacement();

    // ── Getters
    int     getId()          const { return id_emplacement; }
    QString getNumero()      const { return numero; }
    QString getType()        const { return typep_emplacement; }
    QString getEtat()        const { return etate; }
    QDate   getDateDebut()   const { return date_debut_occ; }
    QString getCommentaire() const { return commentaire; }
    int     getIdQuais()     const { return id_quais; }
    int     getIdBateau()    const { return id_bateau; }

    // ── Setters
    void setId(int v)                     { id_emplacement    = v; }
    void setNumero(const QString &v)      { numero            = v; }
    void setType(const QString &v)        { typep_emplacement = v; }
    void setEtat(const QString &v)        { etate             = v; }
    void setDateDebut(const QDate &v)     { date_debut_occ    = v; }
    void setCommentaire(const QString &v) { commentaire       = v; }
    void setIdQuais(int v)                { id_quais          = v; }
    void setIdBateau(int v)               { id_bateau         = v; }

    // ── CRUD
    bool ajouter();
    bool modifier();
    bool supprimer();
    bool afficher(QTableWidget *table);

    // ── Utilitaires
    bool    chargerQuais(QComboBox *combo);
    bool    chargerBateaux(QComboBox *combo);
    void    rechercher(QTableWidget *table, const QString &texte, const QString &critere);
    void    trier(QTableWidget *table, const QString &critere);
    QString genererCommentaire(const QString &type, const QString &etat, const QDate &dateDebut);
    void    exporterPDF(QTableWidget *table, QWidget *parent);
    void    exporterExcel(QTableWidget *table, QWidget *parent);
    void    mettreAJourStatistiques(QTableWidget *table,
                                 QLabel *lblTotal,
                                 QLabel *lblLibres,
                                 QLabel *lblOccupes,
                                 QLabel *lblMaintenance);

    // ── Historique
    void afficherHistorique(QWidget       *container,
                            const QString &filtre    = "tout l'historique",
                            const QString &ordre     = "Récent en premier",
                            const QString &recherche = "");

    // ── Moteur de recommandation IA
    //    ✅ UNE SEULE déclaration — onAttribuer optionnel (nullptr par défaut)
    void afficherRecommandation(
        QWidget *container,
        int      idBateau,
        std::function<void(int, int, const QString&, const QString&,
                           int, const QString&, const QString&)> onAttribuer = nullptr);

    // ── Attribution automatique
    bool attribuerEmplacement(int idEmp, int idBateau);

    void afficherAnalyseSaisonniere(QWidget *parent);


private:
    int     id_emplacement;
    QString numero;
    QString typep_emplacement;
    QString etate;
    QDate   date_debut_occ;
    QString commentaire;
    int     id_quais;
    int     id_bateau;
};

#endif // EMPLACEMENT_H
