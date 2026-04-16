#include "bateau.h"

Bateau::Bateau()
{
}

Bateau::Bateau(QString num, QString nom, QString status, QDate dateF, QString typeB)
{
    this->numero_carte_grise = num;
    this->nomB = nom;
    this->statusB = status;
    this->date_fabrication = dateF;
    this->type = typeB;
}

QString Bateau::getNumeroCarteGrise() const { return numero_carte_grise; }
QString Bateau::getNom() const { return nomB; }
QString Bateau::getStatus() const { return statusB; }
QDate Bateau::getDateFabrication() const { return date_fabrication; }
QString Bateau::getType() const { return type; }

void Bateau::setNumeroCarteGrise(const QString& num) { numero_carte_grise = num; }
void Bateau::setNom(const QString& nom) { nomB = nom; }
void Bateau::setStatus(const QString& status) { statusB = status; }
void Bateau::setDateFabrication(const QDate& date) { date_fabrication = date; }
void Bateau::setType(const QString& typeB) { type = typeB; }

bool Bateau::ajouter()
{
    QSqlQuery query;
    query.prepare("INSERT INTO bateau (numero_carte_grise, nomB, statusB, date_fabrication, type) "
                  "VALUES (:num, :nom, :status, TO_DATE(:dateF, 'YYYY-MM-DD'), :type)");
    query.bindValue(":num", numero_carte_grise);
    query.bindValue(":nom", nomB);
    query.bindValue(":status", statusB);
    query.bindValue(":dateF", date_fabrication.toString("yyyy-MM-dd"));
    query.bindValue(":type", type);

    return query.exec();
}

bool Bateau::modifier(const QString& originalNum)
{
    QSqlQuery query;
    query.prepare("UPDATE bateau SET numero_carte_grise=:newNum, nomB=:nom, statusB=:status, "
                  "date_fabrication=TO_DATE(:dateF,'YYYY-MM-DD'), type=:type "
                  "WHERE numero_carte_grise=:oldNum");
    query.bindValue(":newNum", numero_carte_grise);
    query.bindValue(":nom", nomB);
    query.bindValue(":status", statusB);
    query.bindValue(":dateF", date_fabrication.toString("yyyy-MM-dd"));
    query.bindValue(":type", type);
    query.bindValue(":oldNum", originalNum);

    return query.exec();
}

bool Bateau::supprimer(const QString& num)
{
    QSqlQuery query;
    query.prepare("DELETE FROM bateau WHERE numero_carte_grise=:num");
    query.bindValue(":num", num);
    return query.exec();
}

bool Bateau::existe(const QString& num)
{
    QSqlQuery checkQuery;
    checkQuery.prepare("SELECT numero_carte_grise FROM bateau WHERE numero_carte_grise = :num");
    checkQuery.bindValue(":num", num);
    if (checkQuery.exec() && checkQuery.next()) {
        return true;
    }
    return false;
}

int Bateau::countStatus(const QString &status)
{
    QSqlQuery q;
    q.prepare("SELECT COUNT(*) FROM bateau WHERE statusB = :s");
    q.bindValue(":s", status);
    if (q.exec() && q.next()) {
        return q.value(0).toInt();
    }
    return 0;
}

int Bateau::countSorties(const QString &num)
{
    QSqlQuery q;
    q.prepare("SELECT COUNT(*) FROM avoir WHERE numero_carte_grise = :num");
    q.bindValue(":num", num);
    if (q.exec() && q.next()) {
        return q.value(0).toInt();
    }
    return 0;
}

void Bateau::loadTable(QSqlQueryModel *model)
{
    model->setQuery("SELECT TO_CHAR(numero_carte_grise) AS NUMERO_CARTE_GRISE, "
                    "nomB, statusB, date_fabrication, type, "
                    "TO_CHAR(id_emplacement) AS ID_EMPLACEMENT FROM bateau");
}

void Bateau::filterTable(QSqlQueryModel *model, const QString& search, const QString& status, const QString& type, int triIndex)
{
    QString sql = "SELECT TO_CHAR(numero_carte_grise) AS NUMERO_CARTE_GRISE, "
                  "nomB, statusB, date_fabrication, type, "
                  "TO_CHAR(id_emplacement) AS ID_EMPLACEMENT FROM bateau";

    QStringList conditions;

    if (!search.isEmpty()) {
        QString safe = search;
        safe.replace("'", "''");
        conditions << QString("(UPPER(nomB) LIKE UPPER('%%%1%%') "
                              "OR TO_CHAR(numero_carte_grise) LIKE '%%%1%%')")
                          .arg(safe);
    }

    if (status != "Tous les statuts") {
        QString safe = status;
        safe.replace("'", "''");
        conditions << QString("statusB = '%1'").arg(safe);
    }

    if (type != "Tous les types") {
        QString safe = type;
        safe.replace("'", "''");
        conditions << QString("type = '%1'").arg(safe);
    }

    if (!conditions.isEmpty()) {
        sql += " WHERE " + conditions.join(" AND ");
    }

    if (triIndex == 0) {
        sql += " ORDER BY nomB ASC";
    } else if (triIndex == 1) {
        sql += " ORDER BY nomB DESC";
    } else if (triIndex == 2) {
        sql += " ORDER BY statusB ASC";
    } else if (triIndex == 3) {
        sql += " ORDER BY date_fabrication DESC";
    } else if (triIndex == 4) {
        sql += " ORDER BY date_fabrication ASC";
    }

    model->setQuery(sql);
}
