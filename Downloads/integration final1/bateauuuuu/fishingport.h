#ifndef FISHINGPORT_H
#define FISHINGPORT_H

#include <QMainWindow>
#include <QSqlQueryModel>
#include <QtSql>
#include <QJsonArray>
#include <QJsonDocument>
#include <QJsonObject>
#include <QNetworkAccessManager>
#include <QNetworkReply>
#include <QIcon>
#include <QPushButton>
#include <QTableWidget>
#include <QDialog>
#include <QVBoxLayout>
#include <QHBoxLayout>
#include <QLabel>
#include <QProgressBar>
#include <QComboBox>
#include "bateau.h"
#include "emplacement.h"

QT_BEGIN_NAMESPACE
namespace Ui { class fishingport; }
QT_END_NAMESPACE

class fishingport : public QMainWindow
{
    Q_OBJECT

public:
    fishingport(QWidget *parent = nullptr);
    ~fishingport();
    void loadBateaux();
    void loadStatistiques();

private slots:
    // ── Bateaux ──
    void on_btnAjouter_clicked();
    void on_btnEnregistrer_clicked();
    void on_btnModify_clicked();
    void on_btnDelete_clicked();
    void on_tableBateaux_clicked(const QModelIndex &index);
    void on_pushButton_2_clicked();
    void on_pushButton_clicked();
    void on_btnConsulter_clicked();
    void on_btnAnnuler_clicked();
    void on_btnNavBateaux_clicked();
    void on_btnRefreshStats_clicked();
    void on_btnActionStats_clicked();
    void on_btnRetour_clicked();
    void applyFilters();
    void on_btnSendChat_clicked();
    void on_btnPrediction_clicked();
    void on_btnLaunchPrediction_clicked();
    void on_btnPredictionRetour_clicked();

    // ── Emplacements ──
    void on_pushButton_14_clicked();
    void on_pushButton_15_clicked();
    void on_pushButton_16_clicked();
    void on_pushButton_17_clicked();
    void on_comboBox_11_currentIndexChanged(int index);
    void rechercherEmplacements();
    void trierEmplacements();
    void exporterPDF();
    void exporterExcel();
    void consulterHistorique();
    void rechercherHistorique();
    void afficherFriseDialog(int idEmplacement);
    void lancerRecommandation();
    void afficherModalAttribution(int idEmp, int idBateau,
                                  const QString &numero,
                                  const QString &quai,
                                  int score,
                                  const QString &nomBateau,
                                  const QString &typeBateau);
    void afficherAnalyseSaisonniere();

    // ── Navigation ──
    void on_btnNavEmplacements_clicked();

private:
    Ui::fishingport *ui;

    // Bateaux
    QSqlQueryModel *model;
    bool m_editMode = false;
    QString m_originalNum;
    QNetworkAccessManager *networkManager;
    void sendToOllama(const QString &prompt);

    // Emplacements
    emplacement Etmp;
    bool m_connexionsBranchees      = false;
    bool m_histConnexionsBranchees  = false;

    void afficherEmplacements();
    void ajouterBoutonsAction(int ligne);
    void mettreAJourStatistiquesEmp();
    void voirEmplacement(int row);
    void modifierEmplacement(int row);
    void supprimerEmplacement(int row);
    QIcon creerIcone(const QString &type);
};

#endif // FISHINGPORT_H
