#include "connexion.h"
#include <QDebug>

Connexion::Connexion() {}

bool Connexion::ouvrirConnexion()
{
    db = QSqlDatabase::addDatabase("QODBC");
    db.setDatabaseName(
        "Driver={Oracle in XE};"
        "Dbq=XE;"
        "Uid=inchirah;"
        "Pwd=blackpink;"
        );

    if (db.open()) {
        qDebug() << "Connexion réussie à la base de données !";
        return true;
    } else {
        qDebug() << "Échec de la connexion :" << db.lastError().text();
        return false;
    }
}

void Connexion::fermerConnexion()
{
    if (db.isOpen()) {
        db.close();
        qDebug() << "Connexion fermée.";
    }
}
