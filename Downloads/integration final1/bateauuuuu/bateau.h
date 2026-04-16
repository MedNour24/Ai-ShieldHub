#ifndef BATEAU_H
#define BATEAU_H

#include <QString>
#include <QDate>
#include <QSqlQuery>
#include <QSqlQueryModel>
#include <QVariant>
#include <QSqlError>

class Bateau
{
private:
    QString numero_carte_grise;
    QString nomB;
    QString statusB;
    QDate date_fabrication;
    QString type;

public:
    Bateau();
    Bateau(QString num, QString nom, QString status, QDate dateF, QString typeB);

    // Getters
    QString getNumeroCarteGrise() const;
    QString getNom() const;
    QString getStatus() const;
    QDate getDateFabrication() const;
    QString getType() const;

    // Setters
    void setNumeroCarteGrise(const QString& num);
    void setNom(const QString& nom);
    void setStatus(const QString& status);
    void setDateFabrication(const QDate& date);
    void setType(const QString& type);

    // CRUD Base Methodes
    bool ajouter();
    bool modifier(const QString& originalNum);
    static bool supprimer(const QString& num);

    // Helpers
    static bool existe(const QString& num);
    static int countStatus(const QString &status);
    static int countSorties(const QString &num);
    static void loadTable(QSqlQueryModel *model);
    static void filterTable(QSqlQueryModel *model, const QString& search, const QString& status, const QString& type, int triIndex);
};

#endif // BATEAU_H
