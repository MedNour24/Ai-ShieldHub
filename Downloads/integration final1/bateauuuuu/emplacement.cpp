#include "emplacement.h"

#include <QSqlQuery>
#include <QSqlError>
#include <QMessageBox>
#include <QTableWidgetItem>
#include <QHeaderView>
#include <QLabel>
#include <QPushButton>
#include <QFileDialog>
#include <QTextStream>
#include <QFile>
#include <QPrinter>
#include <QTextDocument>
#include <QPageLayout>
#include <QColor>
#include <QDebug>
#include <QVariant>
#include <QStandardItemModel>
#include <QTableView>
#include <QProgressBar>
#include <QVBoxLayout>
#include <QHBoxLayout>
#include <QFrame>
#include <QScrollArea>
#include <QFont>
#include <algorithm>


// ══════════════════════════════════════════
//  Constructeur
// ══════════════════════════════════════════
emplacement::emplacement()
    : id_emplacement(0),
    id_quais(0),
    id_bateau(0)
{}

// ══════════════════════════════════════════
//  AJOUTER
// ══════════════════════════════════════════
bool emplacement::ajouter()
{
    bool numeroOk;
    numero.toInt(&numeroOk);
    if (!numeroOk || numero.toInt() <= 0) {
        QMessageBox::warning(nullptr, "Numéro invalide",
                             "Le numéro doit être un entier positif !\nEx: 101, 205, 310");
        return false;
    }
    if (id_quais == 0) {
        QMessageBox::warning(nullptr, "Champ manquant", "Veuillez sélectionner un Quai !");
        return false;
    }
    if (etate.isEmpty()) {
        QMessageBox::warning(nullptr, "Champ manquant", "Veuillez sélectionner un État !");
        return false;
    }

    QDate aujourdhui = QDate::currentDate();
    int   ansDate    = date_debut_occ.daysTo(aujourdhui) / 365;

    if (date_debut_occ > aujourdhui) {
        QMessageBox::warning(nullptr, "Date invalide", "La date ne peut pas être dans le futur !");
        return false;
    }
    if (ansDate > 50) {
        QMessageBox::warning(nullptr, "Date trop ancienne",
                             "La date est trop ancienne (" + QString::number(ansDate) +
                                 " ans) !\nVeuillez saisir une date réaliste.");
        return false;
    }

    // ✅ VÉRIFICATION : Empêcher les numéros en double
    QSqlQuery checkQuery;
    checkQuery.prepare("SELECT COUNT(*) FROM emplacement WHERE numero = :numero");
    checkQuery.bindValue(":numero", numero);

    if (checkQuery.exec() && checkQuery.next()) {
        int count = checkQuery.value(0).toInt();
        if (count > 0) {
            QMessageBox::warning(nullptr, "Numéro existant",
                                 "L'emplacement numéro " + numero +
                                     " existe déjà !\nVeuillez saisir un numéro unique.");
            return false;
        }
    }

    int newId = 0;
    QSqlQuery idQuery;
    idQuery.prepare("SELECT NVL(MAX(id_emplacement), 0) + 1 FROM emplacement");
    if (!idQuery.exec() || !idQuery.next()) {
        QMessageBox::critical(nullptr, "Erreur SQL",
                              "Impossible de generer l'ID :\n" + idQuery.lastError().text());
        return false;
    }
    newId = idQuery.value(0).toInt();

    QSqlQuery query;
    query.prepare(
        "INSERT INTO emplacement "
        "(id_emplacement, numero, etatE, date_debut_occ, commentaire, id_quais, typeP_emplacement) "
        "VALUES (:idEmp, :numero, :etat, TO_DATE(:date, 'YYYY-MM-DD'), "
        ":commentaire, :idQuais, :type)");
    query.bindValue(":idEmp",       newId);
    query.bindValue(":numero",      numero);
    query.bindValue(":etat",        etate);
    query.bindValue(":date",        date_debut_occ.toString("yyyy-MM-dd"));
    query.bindValue(":commentaire", commentaire);
    query.bindValue(":idQuais",     id_quais);
    query.bindValue(":type",        typep_emplacement);

    if (!query.exec()) {
        QMessageBox::critical(nullptr, "Erreur SQL",
                              "Ajout non effectué :\n" + query.lastError().text());
        return false;
    }


    if (id_bateau != 0 && newId > 0) {
        QSqlQuery qb;
        qb.prepare("UPDATE bateau SET id_emplacement = :idEmp "
                   "WHERE numero_carte_grise = :idBateau");
        qb.bindValue(":idEmp",    newId);
        qb.bindValue(":idBateau", id_bateau);
        if (!qb.exec()) {
            qDebug() << "Erreur UPDATE bateau:" << qb.lastError().text();
        } else {
            qDebug() << "Bateau" << id_bateau << "associé à l'emplacement" << newId;
        }
    }

    QMessageBox::information(nullptr, "Succès", "Ajout effectué avec succès !");
    return true;
}

// ══════════════════════════════════════════
//  MODIFIER
// ══════════════════════════════════════════
bool emplacement::modifier()
{
    if (id_emplacement == 0) {
        QMessageBox::warning(nullptr, "Erreur", "Aucun emplacement sélectionné pour modification !");
        return false;
    }

    QString ancienEtat;
    int ancienBateau = 0;
    QSqlQuery check;
    check.prepare("SELECT e.etatE, b.numero_carte_grise FROM emplacement e "
                  "LEFT JOIN bateau b ON b.id_emplacement = e.id_emplacement "
                  "WHERE e.id_emplacement = :id");
    check.bindValue(":id", id_emplacement);
    if (check.exec() && check.next()) {
        ancienEtat   = check.value(0).toString();
        ancienBateau = check.value(1).toInt();
    }
    Q_UNUSED(ancienBateau);

    QSqlQuery query;
    query.prepare(
        "UPDATE emplacement SET "
        "  numero            = :numero, "
        "  typeP_emplacement = :type, "
        "  etatE             = :etat, "
        "  date_debut_occ    = TO_DATE(:date, 'YYYY-MM-DD'), "
        "  commentaire       = :commentaire, "
        "  id_quais          = :idQuais "
        "WHERE id_emplacement = :id");
    query.bindValue(":numero",      numero);
    query.bindValue(":type",        typep_emplacement);
    query.bindValue(":etat",        etate);
    query.bindValue(":date",        date_debut_occ.toString("yyyy-MM-dd"));
    query.bindValue(":commentaire", commentaire);
    query.bindValue(":idQuais",     id_quais);
    query.bindValue(":id",          id_emplacement);

    if (!query.exec()) {
        QMessageBox::critical(nullptr, "Erreur SQL",
                              "Modification non effectuée :\n" + query.lastError().text());
        return false;
    }

    if (ancienEtat != etate) {
        QString action;
        if      (etate == "Occupé")      action = "Changement d'état: " + ancienEtat + " → Occupé";
        else if (etate == "Libre")       action = "Changement d'état: " + ancienEtat + " → Libre";
        else if (etate == "Maintenance") action = "Changement d'état: " + ancienEtat + " → Maintenance";
        qDebug() << "Historique:" << action;
    }

    QMessageBox::information(nullptr, "Succès", "Modification effectuée avec succès !");
    return true;
}

// ══════════════════════════════════════════
//  SUPPRIMER
// ══════════════════════════════════════════
bool emplacement::supprimer()
{
    if (id_emplacement == 0) {
        QMessageBox::warning(nullptr, "Erreur", "Aucun emplacement à supprimer !");
        return false;
    }

    QSqlQuery queryBateau;
    queryBateau.prepare("UPDATE bateau SET id_emplacement = NULL WHERE id_emplacement = :id");
    queryBateau.bindValue(":id", id_emplacement);
    queryBateau.exec();

    QSqlQuery query;
    query.prepare("DELETE FROM emplacement WHERE id_emplacement = :id");
    query.bindValue(":id", id_emplacement);

    if (!query.exec()) {
        QMessageBox::critical(nullptr, "Erreur SQL",
                              "Suppression échouée :\n" + query.lastError().text());
        return false;
    }

    QMessageBox::information(nullptr, "Succès", "Emplacement supprimé avec succès !");
    return true;
}

// ══════════════════════════════════════════
//  AFFICHER
// ══════════════════════════════════════════
bool emplacement::afficher(QTableWidget *table)
{
    table->setRowCount(0);

    QSqlQuery query;
    query.prepare(
        "SELECT e.id_emplacement, e.typeP_emplacement, e.numero, q.nom, "
        "       e.etatE, b.nomB, e.date_debut_occ "   // ← nomB au lieu de type
        "FROM emplacement e "
        "LEFT JOIN quais    q ON e.id_quais       = q.id_quais "
        "LEFT JOIN bateau b ON b.id_emplacement = e.id_emplacement "
        "ORDER BY e.id_emplacement");
    if (!query.exec()) {
        QMessageBox::critical(nullptr, "Erreur SQL",
                              "Impossible de charger les emplacements :\n" + query.lastError().text());
        return false;
    }

    int ligne = 0;
    while (query.next()) {
        table->insertRow(ligne);
        table->setItem(ligne, 0, new QTableWidgetItem(query.value(0).toString()));
        table->setItem(ligne, 1, new QTableWidgetItem(query.value(1).toString()));
        table->setItem(ligne, 2, new QTableWidgetItem(query.value(2).toString()));
        table->setItem(ligne, 3, new QTableWidgetItem(query.value(3).toString()));
        table->setItem(ligne, 4, new QTableWidgetItem(query.value(4).toString()));
        QString typeBateau = query.value(5).toString();
        if (typeBateau.isEmpty()) typeBateau = "Aucun";
        table->setItem(ligne, 5, new QTableWidgetItem(typeBateau));
        table->setItem(ligne, 6,
                       new QTableWidgetItem(query.value(6).toDate().toString("dd/MM/yyyy")));
        ligne++;
    }

    table->resizeColumnsToContents();
    table->horizontalHeader()->setStretchLastSection(true);
    table->setEditTriggers(QAbstractItemView::NoEditTriggers);
    return true;
}

// ══════════════════════════════════════════
//  CHARGER QUAIS
// ══════════════════════════════════════════
bool emplacement::chargerQuais(QComboBox *combo)
{
    combo->clear();
    combo->addItem("-- Sélectionner un QUAIS --", 0);

    QSqlQuery query;
    query.prepare("SELECT id_quais, nom FROM quais ORDER BY nom");
    if (!query.exec()) {
        QMessageBox::critical(nullptr, "Erreur",
                              "Impossible de charger les quais :\n" + query.lastError().text());
        return false;
    }
    while (query.next())
        combo->addItem(query.value(1).toString(), query.value(0).toInt());
    return true;
}

// ══════════════════════════════════════════
//  CHARGER BATEAUX
// ══════════════════════════════════════════
bool emplacement::chargerBateaux(QComboBox *combo)
{
    combo->clear();
    combo->addItem("Aucun bateau", 0);

    QSqlQuery query;
    query.prepare("SELECT numero_carte_grise, type FROM bateau ORDER BY type");
    if (!query.exec()) {
        QMessageBox::critical(nullptr, "Erreur",
                              "Impossible de charger les bateaux :\n" + query.lastError().text());
        return false;
    }
    while (query.next())
        combo->addItem(query.value(1).toString(), query.value(0).toInt());
    return true;
}

// ══════════════════════════════════════════
//  RECHERCHER
// ══════════════════════════════════════════
void emplacement::rechercher(QTableWidget *table,
                             const QString &texte,
                             const QString &critere)
{
    QString t = texte.trimmed().toLower();
    // trimmed() = supprimer les espaces au début/fin
    // toLower() = mettre en minuscules pour comparaison insensible à la casse

    for (int i = 0; i < table->rowCount(); i++) {
        bool    visible = true;
        QString valeur  = "";

        if (!t.isEmpty()) {
            if      (critere == "ID")     valeur = table->item(i,0) ? table->item(i,0)->text().toLower() : "";
            else if (critere == "Type")   valeur = table->item(i,1) ? table->item(i,1)->text().toLower() : "";
            else if (critere == "Numéro") valeur = table->item(i,2) ? table->item(i,2)->text().toLower() : "";
            else if (critere == "État")   valeur = table->item(i,4) ? table->item(i,4)->text().toLower() : "";
            else if (critere == "Durée") {
                QString ds = table->item(i,6) ? table->item(i,6)->text() : "";
                QDate d = QDate::fromString(ds, "dd/MM/yyyy");
                if (d.isValid())
                    valeur = QString::number(d.daysTo(QDate::currentDate()));
            } else {
                for (int col = 0; col < 7; col++) {
                    if (table->item(i,col) &&
                        table->item(i,col)->text().toLower().contains(t)) {
                        valeur = t; break;
                    }
                }
            }
            visible = valeur.contains(t);
        }
        table->setRowHidden(i, !visible);
    }
}

// ══════════════════════════════════════════
//  TRIER
// ══════════════════════════════════════════
void emplacement::trier(QTableWidget *table, const QString &critere)
{
    if (critere.isEmpty() || critere == "trier par:") return;

    Qt::SortOrder ordre = critere.startsWith(QChar(0x2191))
                              ? Qt::AscendingOrder
                              : Qt::DescendingOrder;
    QString nom = critere.mid(2);

    int colonne = -1;
    if      (nom == "ID")             colonne = 0;
    else if (nom == "État")           colonne = 4;
    else if (nom == "Bateau affecté") colonne = 5;

    if (colonne >= 0)
        table->sortItems(colonne, ordre);
}

// ══════════════════════════════════════════
//  GÉNÉRER COMMENTAIRE
// ══════════════════════════════════════════
QString emplacement::genererCommentaire(const QString &type,
                                        const QString &etat,
                                        const QDate   &dateDebut)
{
    if (type.isEmpty() || type == "Aucun type" || etat.isEmpty())
        return "";

    int jours = dateDebut.daysTo(QDate::currentDate());
    int ans   = jours / 365;
    int mois  = (jours % 365) / 30;

    QString c = "Emplacement de " + type.toLower() + " ";

    if (etat == "Occupé") {
        c += "occupé";
        if      (ans  > 0) c += " depuis " + QString::number(ans)   + " an(s)";
        else if (mois > 0) c += " depuis " + QString::number(mois)  + " mois";
        else               c += " depuis " + QString::number(jours) + " jour(s)";
    } else if (etat == "Libre") {
        c += "libre";
        if      (ans  > 0) c += " depuis " + QString::number(ans)  + " an(s)";
        else if (mois > 0) c += " depuis " + QString::number(mois) + " mois";
    } else if (etat == "Maintenance") {
        c += "en maintenance";
        if (jours > 0) c += " depuis " + QString::number(jours) + " jour(s)";
    }
    c += ". ";

    if      (etat == "Maintenance")        c += "Inspection technique requise avant remise en service.";
    else if (etat == "Occupé" && ans >= 2) c += "Inspection de routine recommandée avant renouvellement.";
    else if (etat == "Occupé" && ans < 2)  c += "Occupation récente, aucune action requise.";
    else if (etat == "Libre"  && ans >= 1) c += "Disponible depuis plus d'un an, mise en valeur conseillée.";
    else if (etat == "Libre")              c += "Disponible et prêt à accueillir une embarcation.";

    return c;
}

// ══════════════════════════════════════════
//  EXPORTER PDF
// ══════════════════════════════════════════
void emplacement::exporterPDF(QTableWidget *table, QWidget *parent)
{
    QString fichier = QFileDialog::getSaveFileName(
        parent, "Exporter en PDF", "emplacements.pdf", "PDF (*.pdf)");
    if (fichier.isEmpty()) return;

    QString html =
        "<h2 style='color:#0288D1;'>Liste des Emplacements</h2>"
        "<table border='1' cellspacing='0' cellpadding='6' "
        "style='border-collapse:collapse; width:100%; font-family:Arial;'>"
        "<tr style='background-color:#0288D1; color:white;'>"
        "<th>ID</th><th>Type</th><th>Numéro</th><th>Quais</th>"
        "<th>État</th><th>Bateau</th><th>Date Début</th></tr>";

    for (int i = 0; i < table->rowCount(); i++) {
        if (table->isRowHidden(i)) continue;
        QString bg = (i % 2 == 0) ? "#FFFFFF" : "#E3F2FD";
        html += QString("<tr style='background-color:%1;'>").arg(bg);
        for (int col = 0; col < 7; col++) {
            QString val = table->item(i, col) ? table->item(i, col)->text() : "";
            html += "<td>" + val + "</td>";
        }
        html += "</tr>";
    }
    html += "</table><p style='color:gray; font-size:10px;'>Exporté le : "
            + QDate::currentDate().toString("dd/MM/yyyy") + "</p>";

    QPrinter printer(QPrinter::HighResolution);
    printer.setOutputFormat(QPrinter::PdfFormat);
    printer.setOutputFileName(fichier);
    printer.setPageOrientation(QPageLayout::Landscape);

    QTextDocument doc;
    doc.setHtml(html);
    doc.print(&printer);

    QMessageBox::information(parent, "Export PDF", "PDF exporté avec succès !\n" + fichier);
}

// ══════════════════════════════════════════
//  EXPORTER EXCEL (CSV)
// ══════════════════════════════════════════
void emplacement::exporterExcel(QTableWidget *table, QWidget *parent)
{
    QString fichier = QFileDialog::getSaveFileName(
        parent, "Exporter en Excel", "emplacements.csv",
        "CSV Excel (*.csv);;Tous les fichiers (*)");
    if (fichier.isEmpty()) return;

    QFile file(fichier);
    if (!file.open(QIODevice::WriteOnly | QIODevice::Text)) {
        QMessageBox::critical(parent, "Erreur", "Impossible de créer le fichier !");
        return;
    }

    QTextStream out(&file);
    out.setEncoding(QStringConverter::Utf8);
    out << "ID;Type;Numéro;Quais;État;Bateau Affecté;Date Début\n";

    for (int i = 0; i < table->rowCount(); i++) {
        if (table->isRowHidden(i)) continue;
        QStringList ligne;
        for (int col = 0; col < 7; col++)
            ligne << (table->item(i, col) ? table->item(i, col)->text() : "");
        out << ligne.join(";") << "\n";
    }
    file.close();
    QMessageBox::information(parent, "Export Excel", "Fichier CSV exporté !\n" + fichier);
}

// ══════════════════════════════════════════
//  STATISTIQUES
// ══════════════════════════════════════════
void emplacement::mettreAJourStatistiques(QTableWidget *table,
                                          QLabel *lblTotal,
                                          QLabel *lblLibres,
                                          QLabel *lblOccupes,
                                          QLabel *lblMaintenance)
{
    int total = 0, libres = 0, occupes = 0, maintenance = 0;

    for (int i = 0; i < table->rowCount(); i++) {
        if (table->isRowHidden(i)) continue;
        total++;
        QString etat = table->item(i, 4) ? table->item(i, 4)->text() : "";
        if      (etat == "Libre")       libres++;
        else if (etat == "Occupé")      occupes++;
        else if (etat == "Maintenance") maintenance++;
    }

    if (lblTotal)       lblTotal->setText("📊Total: "       + QString::number(total));
    if (lblLibres)      lblLibres->setText("🟢Libres: "     + QString::number(libres));
    if (lblOccupes)     lblOccupes->setText("🔴Occupés: "   + QString::number(occupes));
    if (lblMaintenance) lblMaintenance->setText("🟡Maintenance: " + QString::number(maintenance));
}

// ══════════════════════════════════════════
//  AFFICHER HISTORIQUE
// ══════════════════════════════════════════
void emplacement::afficherHistorique(QWidget       *container,
                                     const QString &filtre,
                                     const QString &ordre,
                                     const QString &recherche)
{
    if (container->layout()) {
        QLayout *lay = container->layout();
        QLayoutItem *item;
        while ((item = lay->takeAt(0)) != nullptr) {
            if (item->widget()) item->widget()->deleteLater();
            delete item;
        }
        delete lay;
    }

    QDate aujourd = QDate::currentDate();
    QString where = "";

    if (filtre == "ce mois") {
        where = " AND EXTRACT(MONTH FROM e.date_debut_occ) = "
                + QString::number(aujourd.month())
                + " AND EXTRACT(YEAR FROM e.date_debut_occ) = "
                + QString::number(aujourd.year());
    } else if (filtre == "cette semaine") {
        QDate lundi = aujourd.addDays(-(aujourd.dayOfWeek() - 1));
        QDate dim   = lundi.addDays(6);
        where = " AND e.date_debut_occ >= TO_DATE('"
                + lundi.toString("yyyy-MM-dd") + "','YYYY-MM-DD')"
                                                 " AND e.date_debut_occ <= TO_DATE('"
                + dim.toString("yyyy-MM-dd") + "','YYYY-MM-DD')";
    }

    QString sortDir = (ordre == "Ancien en premier") ? "ASC" : "DESC";

    QString sql =
        "SELECT e.id_emplacement, e.numero, q.nom, b.nomB, "
        "       e.etatE, e.date_debut_occ, e.typeP_emplacement, e.commentaire "
        "FROM emplacement e "
        "LEFT JOIN quais q ON e.id_quais       = q.id_quais "
        "LEFT JOIN bateau b ON b.id_emplacement = e.id_emplacement "
        "WHERE 1=1 " + where +
        " ORDER BY e.date_debut_occ " + sortDir;

    QSqlQuery query;
    if (!query.exec(sql)) {
        QMessageBox::critical(nullptr, "Erreur SQL", query.lastError().text());
        return;
    }

    struct Entree {
        int     id;
        QString numero, quai, bateau, etat, type, commentaire;
        QDate   date;
        int     jours;
    };

    QList<Entree> entrees;
    QString r = recherche.trimmed().toLower();

    while (query.next()) {
        Entree e;
        e.id          = query.value(0).toInt();
        e.numero      = query.value(1).toString();
        e.quai        = query.value(2).toString();
        e.bateau      = query.value(3).toString();
        if (e.bateau.isEmpty()) e.bateau = "Aucun";
        e.etat        = query.value(4).toString();
        e.date        = query.value(5).toDate();
        e.type        = query.value(6).toString();
        e.commentaire = query.value(7).toString();
        e.jours       = e.date.daysTo(aujourd);

        if (!r.isEmpty()) {
            bool found = e.numero.toLower().contains(r) ||
                         e.quai.toLower().contains(r)   ||
                         e.bateau.toLower().contains(r) ||
                         e.etat.toLower().contains(r)   ||
                         e.type.toLower().contains(r);
            if (!found) continue;
        }
        entrees << e;
    }

    QVBoxLayout *outerLayout = new QVBoxLayout(container);
    outerLayout->setContentsMargins(0, 0, 0, 0);
    outerLayout->setSpacing(0);

    QScrollArea *scroll = new QScrollArea();
    scroll->setWidgetResizable(true);
    scroll->setFrameShape(QFrame::NoFrame);
    scroll->setHorizontalScrollBarPolicy(Qt::ScrollBarAlwaysOff);
    scroll->setStyleSheet(
        "QScrollArea { background: transparent; border: none; }"
        "QScrollBar:vertical { background: #ECEFF1; width: 7px; border-radius: 3px; }"
        "QScrollBar::handle:vertical { background: #B0BEC5; border-radius: 3px; min-height: 28px; }"
        "QScrollBar::add-line:vertical, QScrollBar::sub-line:vertical { height: 0; }");

    QWidget *inner = new QWidget();
    inner->setStyleSheet("background: transparent;");
    QVBoxLayout *list = new QVBoxLayout(inner);
    list->setContentsMargins(14, 10, 14, 14);
    list->setSpacing(0);

    if (entrees.isEmpty()) {
        QLabel *vide = new QLabel("Aucun enregistrement trouvé pour ce filtre.");
        vide->setAlignment(Qt::AlignCenter);
        vide->setStyleSheet(
            "color: #90A4AE; font-size: 13px; font-style: italic; padding: 50px 20px;");
        list->addWidget(vide);
        list->addStretch();
        scroll->setWidget(inner);
        outerLayout->addWidget(scroll);
        return;
    }

    for (int i = 0; i < entrees.size(); i++) {
        const Entree &e = entrees[i];

        QString dotColor, badgeBg, badgeText, leftBorder, cardBg, dateColor;
        if (e.etat == "Libre") {
            dotColor = "#4CAF50"; badgeBg = "#E8F5E9"; badgeText = "#2E7D32";
            leftBorder = "#66BB6A"; cardBg = "#F9FFF9"; dateColor = "#43A047";
        } else if (e.etat == "Occupé") {
            dotColor = "#EF5350"; badgeBg = "#FFEBEE"; badgeText = "#B71C1C";
            leftBorder = "#EF5350"; cardBg = "#FFFAFA"; dateColor = "#E53935";
        } else if (e.etat == "Maintenance") {
            dotColor = "#FF9800"; badgeBg = "#FFF3E0"; badgeText = "#E65100";
            leftBorder = "#FFA726"; cardBg = "#FFFEF7"; dateColor = "#FB8C00";
        } else {
            dotColor = "#90A4AE"; badgeBg = "#ECEFF1"; badgeText = "#546E7A";
            leftBorder = "#B0BEC5"; cardBg = "#FAFAFA"; dateColor = "#78909C";
        }

        QHBoxLayout *rowLayout = new QHBoxLayout();
        rowLayout->setContentsMargins(0, 0, 0, 0);
        rowLayout->setSpacing(12);

        QWidget *tlWidget = new QWidget();
        tlWidget->setFixedWidth(24);
        tlWidget->setStyleSheet("background: transparent;");
        QVBoxLayout *tlLayout = new QVBoxLayout(tlWidget);
        tlLayout->setContentsMargins(0, 0, 0, 0);
        tlLayout->setSpacing(0);
        tlLayout->setAlignment(Qt::AlignHCenter);

        QFrame *lineTop = new QFrame();
        lineTop->setFixedWidth(2);
        lineTop->setMinimumHeight(16);
        lineTop->setStyleSheet(i == 0 ? "background: transparent;" : "background: #CFD8DC;");
        tlLayout->addWidget(lineTop, 0, Qt::AlignHCenter);

        QLabel *dot = new QLabel();
        dot->setFixedSize(16, 16);
        dot->setStyleSheet(QString(
                               "background: %1; border-radius: 8px; border: 2px solid white; margin: 0;")
                               .arg(dotColor));
        tlLayout->addWidget(dot, 0, Qt::AlignHCenter);

        QFrame *lineBot = new QFrame();
        lineBot->setFixedWidth(2);
        lineBot->setMinimumHeight(16);
        lineBot->setStyleSheet(
            i == entrees.size()-1 ? "background: transparent;" : "background: #CFD8DC;");
        tlLayout->addWidget(lineBot, 0, Qt::AlignHCenter);
        rowLayout->addWidget(tlWidget);

        QFrame *card = new QFrame();
        card->setStyleSheet(QString(
                                "QFrame { background: %1; border-radius: 12px; border-left: 4px solid %2; "
                                "border-top: 1px solid #E0E0E0; border-right: 1px solid #E0E0E0; "
                                "border-bottom: 1px solid #E0E0E0; margin-top: 4px; margin-bottom: 4px; }"
                                ).arg(cardBg, leftBorder));

        QVBoxLayout *cardLayout = new QVBoxLayout(card);
        cardLayout->setContentsMargins(14, 10, 14, 10);
        cardLayout->setSpacing(4);

        QHBoxLayout *topRow = new QHBoxLayout();
        topRow->setSpacing(6);

        QLabel *dateLbl = new QLabel(e.date.toString("dd MMMM yyyy"));
        dateLbl->setStyleSheet(QString(
                                   "color: %1; font-size: 11px; font-weight: 600; "
                                   "background: transparent; border: none;").arg(dateColor));
        topRow->addWidget(dateLbl);
        topRow->addStretch();

        QLabel *badge = new QLabel(e.etat);
        badge->setStyleSheet(QString(
                                 "background: %1; color: %2; border-radius: 7px; border: none; "
                                 "font-size: 10px; font-weight: bold; padding: 2px 10px;")
                                 .arg(badgeBg, badgeText));
        topRow->addWidget(badge);
        cardLayout->addLayout(topRow);

        QLabel *titleLbl = new QLabel(
            QString("Emp. %1  →  %2").arg(e.numero, e.type.isEmpty() ? "—" : e.type));
        titleLbl->setStyleSheet(
            "font-size: 13px; font-weight: bold; color: #263238; "
            "background: transparent; border: none;");
        cardLayout->addWidget(titleLbl);

        QLabel *detailLbl = new QLabel(
            QString("Bateau : %1   ·   Quai : %2   ·   Durée : %3 jour(s)")
                .arg(e.bateau, e.quai.isEmpty() ? "—" : e.quai,
                     QString::number(qMax(0, e.jours))));
        detailLbl->setStyleSheet(
            "font-size: 11px; color: #78909C; background: transparent; border: none;");
        cardLayout->addWidget(detailLbl);

        if (!e.commentaire.isEmpty()) {
            QLabel *commLbl = new QLabel(e.commentaire);
            commLbl->setWordWrap(true);
            commLbl->setStyleSheet(
                "font-size: 11px; color: #B0BEC5; font-style: italic; "
                "background: transparent; border: none;");
            cardLayout->addWidget(commLbl);
        }

        rowLayout->addWidget(card, 1);
        list->addLayout(rowLayout);
    }

    list->addStretch();
    scroll->setWidget(inner);
    outerLayout->addWidget(scroll);
}

// ══════════════════════════════════════════
//  MOTEUR DE RECOMMANDATION IA
// ══════════════════════════════════════════
void emplacement::afficherRecommandation(QWidget *container, int idBateau,
                                         std::function<void(int, int, const QString&, const QString&,
                                                            int, const QString&, const QString&)> onAttribuer)
{
    if (container->layout()) {
        QLayout *lay = container->layout();
        QLayoutItem *item;
        while ((item = lay->takeAt(0)) != nullptr) {
            if (item->widget()) item->widget()->deleteLater();
            delete item;
        }
        delete lay;
    }

    QVBoxLayout *outerLayout = new QVBoxLayout(container);
    outerLayout->setContentsMargins(4, 4, 4, 4);
    outerLayout->setSpacing(8);

    if (idBateau == 0) {
        QScrollArea *scroll = new QScrollArea();
        scroll->setWidgetResizable(true);
        scroll->setFrameShape(QFrame::NoFrame);
        scroll->setStyleSheet("QScrollArea { background: transparent; border: none; }");

        QWidget *inner = new QWidget();
        inner->setStyleSheet("background: transparent;");
        QVBoxLayout *iL = new QVBoxLayout(inner);

        QLabel *hint = new QLabel(
            "Sélectionnez un bateau ci-dessus\npour voir les emplacements recommandés.");
        hint->setAlignment(Qt::AlignCenter);
        hint->setStyleSheet(
            "color: #90A4AE; font-size: 12px; font-style: italic; "
            "padding: 40px 20px; background: transparent; border: none;");
        iL->addWidget(hint);
        iL->addStretch();

        scroll->setWidget(inner);
        outerLayout->addWidget(scroll);
        return;
    }

    // Récupérer infos du bateau — colonne TYPE entre guillemets pour Oracle
    QString nomBateau  = "";
    QString typeBateau = "";
    int     quaiBateau = 0;

    QSqlQuery qBateau;
    qBateau.prepare(
        "SELECT b.nomB, b.type, e.id_quais "
        "FROM bateau b "
        "LEFT JOIN emplacement e ON b.id_emplacement = e.id_emplacement "
        "WHERE b.numero_carte_grise = :id");
    qBateau.bindValue(":id", idBateau);
    if (qBateau.exec() && qBateau.next()) {
        nomBateau  = qBateau.value(0).toString();
        typeBateau = qBateau.value(1).toString();
        quaiBateau = qBateau.value(2).toInt();
    }

    QFrame *header = new QFrame();
    header->setStyleSheet(
        "QFrame { background: #E3F2FD; border-radius: 10px; border: 1px solid #B3E5FC; }");
    QVBoxLayout *hL = new QVBoxLayout(header);
    hL->setContentsMargins(12, 8, 12, 8);
    hL->setSpacing(2);

    QLabel *hTitle = new QLabel("Analyse pour : " + nomBateau);
    hTitle->setStyleSheet(
        "font-size: 12px; font-weight: bold; color: #0D47A1; "
        "background: transparent; border: none;");

    QLabel *hSub = new QLabel(
        "Type : " + (typeBateau.isEmpty() ? "—" : typeBateau) +
        "   ·   Algorithme IA actif");
    hSub->setStyleSheet(
        "font-size: 10px; color: #546E7A; background: transparent; border: none;");

    hL->addWidget(hTitle);
    hL->addWidget(hSub);
    outerLayout->addWidget(header);

    // Récupérer emplacements libres + calcul score
    QSqlQuery qEmp;
    qEmp.prepare(
        "SELECT e.id_emplacement, e.numero, e.typeP_emplacement, "
        "       q.nom, e.date_debut_occ, e.id_quais "
        "FROM emplacement e "
        "LEFT JOIN quais q ON e.id_quais = q.id_quais "
        "WHERE e.etatE = 'Libre' "
        "ORDER BY e.id_emplacement");

    struct Candidat {
        int     id;
        QString numero, type, quai;
        QDate   dateLibre;
        int     idQuais;
        int     score;
    };

    QList<Candidat> candidats;
    QDate aujourd = QDate::currentDate();

    if (qEmp.exec()) {
        while (qEmp.next()) {
            Candidat c;
            c.id        = qEmp.value(0).toInt();
            c.numero    = qEmp.value(1).toString();
            c.type      = qEmp.value(2).toString();
            c.quai      = qEmp.value(3).toString();
            c.dateLibre = qEmp.value(4).toDate();
            c.idQuais   = qEmp.value(5).toInt();
            c.score     = 0;

            if (!typeBateau.isEmpty() && !c.type.isEmpty()) {
                if (c.type.toLower() == typeBateau.toLower())
                    c.score += 40;
                else if (c.type.toLower().contains(typeBateau.toLower()) ||
                         typeBateau.toLower().contains(c.type.toLower()))
                    c.score += 20;
            }

            if (quaiBateau != 0 && c.idQuais == quaiBateau)
                c.score += 25;

            int joursLibre = c.dateLibre.daysTo(aujourd);
            if      (joursLibre >= 1   && joursLibre <= 30)  c.score += 20;
            else if (joursLibre > 30   && joursLibre <= 90)  c.score += 15;
            else if (joursLibre > 90   && joursLibre <= 365) c.score += 10;
            else if (joursLibre > 365)                        c.score += 5;

            if      (joursLibre > 180) c.score += 15;
            else if (joursLibre > 60)  c.score += 8;
            else                       c.score += 3;

            candidats.append(c);
        }
    }

    std::sort(candidats.begin(), candidats.end(),
              [](const Candidat &a, const Candidat &b) { return a.score > b.score; });

    QScrollArea *scroll = new QScrollArea();
    scroll->setWidgetResizable(true);
    scroll->setFrameShape(QFrame::NoFrame);
    scroll->setHorizontalScrollBarPolicy(Qt::ScrollBarAlwaysOff);
    scroll->setStyleSheet(
        "QScrollArea { background: transparent; border: none; }"
        "QScrollBar:vertical { background: #ECEFF1; width: 7px; border-radius: 3px; }"
        "QScrollBar::handle:vertical { background: #B0BEC5; border-radius: 3px; min-height: 28px; }"
        "QScrollBar::add-line:vertical, QScrollBar::sub-line:vertical { height: 0; }");

    QWidget *inner = new QWidget();
    inner->setStyleSheet("background: transparent;");
    QVBoxLayout *list = new QVBoxLayout(inner);
    list->setContentsMargins(0, 4, 0, 4);
    list->setSpacing(8);

    if (candidats.isEmpty()) {
        QLabel *vide = new QLabel("Aucun emplacement libre disponible.");
        vide->setAlignment(Qt::AlignCenter);
        vide->setStyleSheet(
            "color: #90A4AE; font-size: 12px; font-style: italic; padding: 30px;");
        list->addWidget(vide);
        list->addStretch();
        scroll->setWidget(inner);
        outerLayout->addWidget(scroll);
        return;
    }

    int scoreMax = candidats.first().score;

    QLabel *secTitle = new QLabel(
        QString("%1 emplacement(s) libre(s) analysé(s)").arg(candidats.size()));
    secTitle->setStyleSheet(
        "font-size: 10px; font-weight: bold; color: #78909C; "
        "background: transparent; border: none;");
    list->addWidget(secTitle);

    QStringList medailles = {"🥇", "🥈", "🥉"};

    for (int i = 0; i < candidats.size(); i++) {
        const Candidat &c = candidats[i];

        int scorePct = (scoreMax > 0) ? qMin(c.score * 100 / scoreMax, 100) : 0;
        if (i == 0) scorePct = 100;

        QString barColor, bgColor, borderColor, textColor;
        if (i == 0) {
            barColor = "#4CAF50"; bgColor = "#F9FFF9";
            borderColor = "#66BB6A"; textColor = "#2E7D32";
        } else if (i == 1) {
            barColor = "#2196F3"; bgColor = "#F0F8FF";
            borderColor = "#64B5F6"; textColor = "#0D47A1";
        } else if (i == 2) {
            barColor = "#FF9800"; bgColor = "#FFFEF7";
            borderColor = "#FFA726"; textColor = "#E65100";
        } else {
            barColor = "#90A4AE"; bgColor = "#FAFAFA";
            borderColor = "#B0BEC5"; textColor = "#546E7A";
        }

        QFrame *card = new QFrame();
        card->setStyleSheet(QString(
                                "QFrame { background: %1; border-radius: 10px; "
                                "border-left: 4px solid %2; "
                                "border-top: 1px solid #E0E0E0; "
                                "border-right: 1px solid #E0E0E0; "
                                "border-bottom: 1px solid #E0E0E0; }"
                                ).arg(bgColor, borderColor));

        QVBoxLayout *cardL = new QVBoxLayout(card);
        cardL->setContentsMargins(12, 8, 12, 8);
        cardL->setSpacing(4);

        QHBoxLayout *row1 = new QHBoxLayout();
        row1->setSpacing(6);

        QString medal = (i < 3) ? medailles[i] + "  " : "    ";
        QLabel *numLbl = new QLabel(medal + "Emp. " + c.numero + "  —  " + c.quai);
        numLbl->setStyleSheet(QString(
                                  "font-size: 12px; font-weight: bold; color: %1; "
                                  "background: transparent; border: none;").arg(textColor));
        row1->addWidget(numLbl);
        row1->addStretch();

        QLabel *scoreLbl = new QLabel(QString::number(scorePct) + "%");
        scoreLbl->setStyleSheet(QString(
                                    "font-size: 13px; font-weight: bold; color: %1; "
                                    "background: transparent; border: none;").arg(textColor));
        row1->addWidget(scoreLbl);
        cardL->addLayout(row1);

        QProgressBar *bar = new QProgressBar();
        bar->setRange(0, 100);
        bar->setValue(scorePct);
        bar->setFixedHeight(7);
        bar->setTextVisible(false);
        bar->setStyleSheet(QString(
                               "QProgressBar { border-radius: 3px; background: #ECEFF1; border: none; }"
                               "QProgressBar::chunk { border-radius: 3px; background: %1; }"
                               ).arg(barColor));
        cardL->addWidget(bar);

        int joursLibre = c.dateLibre.daysTo(aujourd);
        QLabel *detailLbl = new QLabel(
            QString("Type : %1   ·   Libre depuis %2 jour(s)")
                .arg(c.type.isEmpty() ? "—" : c.type)
                .arg(joursLibre));
        detailLbl->setStyleSheet(
            "font-size: 10px; color: #90A4AE; background: transparent; border: none;");
        cardL->addWidget(detailLbl);

        // ✅ Bouton Attribuer pour TOUS les candidats (pas seulement le 1er)
        QHBoxLayout *btnRow = new QHBoxLayout();

        if (i == 0) {
            QLabel *badge2 = new QLabel("  Meilleur choix recommandé  ");
            badge2->setStyleSheet(
                "background: #E8F5E9; color: #2E7D32; border-radius: 6px; "
                "font-size: 10px; font-weight: bold; padding: 2px 8px; border: none;");
            btnRow->addWidget(badge2);
        }
        btnRow->addStretch();

        if (onAttribuer) {
            QPushButton *btnAttrib = new QPushButton("Attribuer ce slot →");
            QString bg  = (i==0) ? "#2E7D32" : (i==1) ? "#0D47A1" : "#E65100";
            QString hov = (i==0) ? "#1B5E20" : (i==1) ? "#0A2E6E" : "#BF360C";
            btnAttrib->setStyleSheet(QString(
                                         "QPushButton { background:%1; color:white; border-radius:7px; "
                                         "border:none; padding:5px 12px; font-size:11px; font-weight:bold; }"
                                         "QPushButton:hover { background:%2; }").arg(bg, hov));

            int     cId     = c.id;
            QString cNumero = c.numero;
            QString cQuai   = c.quai;
            int     cScore  = scorePct;
            QString cNomB   = nomBateau;
            QString cType   = typeBateau;

            QObject::connect(btnAttrib, &QPushButton::clicked, container, [=]() {
                onAttribuer(cId, idBateau, cNumero, cQuai, cScore, cNomB, cType);
            });
            btnRow->addWidget(btnAttrib);
        }
        cardL->addLayout(btnRow);

        list->addWidget(card);
    }

    list->addStretch();
    scroll->setWidget(inner);
    outerLayout->addWidget(scroll);
}

// ══════════════════════════════════════════
//  ATTRIBUER EMPLACEMENT
//  Met à jour emplacement (Occupé + commentaire)
//  et lie le bateau à cet emplacement
// ══════════════════════════════════════════
bool emplacement::attribuerEmplacement(int idEmp, int idBateau)
{
    if (idEmp == 0 || idBateau == 0) {
        QMessageBox::warning(nullptr, "Erreur", "Données manquantes pour l'attribution !");
        return false;
    }

    // Requête 1 : passer l'emplacement à Occupé + commentaire automatique daté
    QSqlQuery q1;
    q1.prepare(
        "UPDATE emplacement "
        "SET etatE = 'Occupé', "
        "    commentaire = 'Attribué automatiquement le ' || TO_CHAR(SYSDATE, 'DD/MM/YYYY') "
        "WHERE id_emplacement = :idEmp");
    q1.bindValue(":idEmp", idEmp);

    if (!q1.exec()) {
        QMessageBox::critical(nullptr, "Erreur SQL",
                              "Mise à jour emplacement échouée :\n" + q1.lastError().text());
        return false;
    }

    // Requête 2 : lier le bateau à cet emplacement
    QSqlQuery q2;
    q2.prepare(
        "UPDATE bateau "
        "SET id_emplacement = :idEmp "
        "WHERE numero_carte_grise = :idBateau");
    q2.bindValue(":idEmp",    idEmp);
    q2.bindValue(":idBateau", idBateau);

    if (!q2.exec()) {
        QMessageBox::critical(nullptr, "Erreur SQL",
                              "Mise à jour bateau échouée :\n" + q2.lastError().text());
        return false;
    }

    return true;
}


// ══════════════════════════════════════════
//  ANALYSE SAISONNIÈRE
// ══════════════════════════════════════════
void emplacement::afficherAnalyseSaisonniere(QWidget *parent)
{
    // ── 1. Requête SQL : occupation par mois ──────────────────
    QSqlQuery query;
    query.prepare(
        "SELECT EXTRACT(MONTH FROM date_debut_occ) AS mois, "
        "       COUNT(*) AS total, "
        "       SUM(CASE WHEN etatE = 'Occupé' THEN 1 ELSE 0 END) AS occupes "
        "FROM emplacement "
        "GROUP BY EXTRACT(MONTH FROM date_debut_occ) "
        "ORDER BY mois");

    // tauxParMois[1..12] = pourcentage d'occupation
    QMap<int, int> tauxParMois;
    if (query.exec()) {
        while (query.next()) {
            int mois    = query.value(0).toInt();
            int total   = query.value(1).toInt();
            int occupes = query.value(2).toInt();
            if (total > 0)
                tauxParMois[mois] = occupes * 100 / total;
            else
                tauxParMois[mois] = 0;
        }
    }

    // ── 2. Calcul taux par saison ─────────────────────────────
    // Printemps : Mars(3) Avr(4) Mai(5)
    // Été       : Jun(6)  Jul(7) Aoû(8)
    // Automne   : Sep(9)  Oct(10) Nov(11)
    // Hiver     : Déc(12) Jan(1)  Fév(2)
    auto moyenneSaison = [&](QList<int> mois) -> int {
        int sum = 0, count = 0;
        for (int m : mois) {
            if (tauxParMois.contains(m)) { sum += tauxParMois[m]; count++; }
        }
        return count > 0 ? sum / count : 0;
    };

    int tauxPrintemps = moyenneSaison({3, 4, 5});
    int tauxEte       = moyenneSaison({6, 7, 8});
    int tauxAutomne   = moyenneSaison({9, 10, 11});
    int tauxHiver     = moyenneSaison({12, 1, 2});

    // ── 3. Compter total emplacements pour suggestions ────────
    QSqlQuery qTotal;
    int totalEmplacements = 0;
    if (qTotal.exec("SELECT COUNT(*) FROM emplacement") && qTotal.next())
        totalEmplacements = qTotal.value(0).toInt();

    // ── 4. Créer le QDialog ───────────────────────────────────
    QDialog *dlg = new QDialog(parent);
    dlg->setWindowTitle("Analyse de tendances saisonnières");
    dlg->resize(680, 620);
    dlg->setStyleSheet(
        "QDialog { background: #F8FAFB; }"
        "QLabel  { border: none; background: transparent; }"
        "QFrame  { border: none; }");

    QVBoxLayout *root = new QVBoxLayout(dlg);
    root->setContentsMargins(20, 16, 20, 16);
    root->setSpacing(14);

    // ── Header ────────────────────────────────────────────────
    QFrame *headerFrame = new QFrame();
    headerFrame->setStyleSheet(
        "QFrame { background: #EEF4FF; border-radius: 12px; border: 1px solid #C7D9FF; }");
    QHBoxLayout *headerL = new QHBoxLayout(headerFrame);
    headerL->setContentsMargins(16, 12, 16, 12);

    QLabel *headerIcon = new QLabel("📅");
    headerIcon->setStyleSheet("font-size: 22px;");
    headerIcon->setFixedWidth(32);

    QVBoxLayout *headerText = new QVBoxLayout();
    QLabel *headerTitle = new QLabel("Analyse de tendances saisonnières");
    headerTitle->setStyleSheet(
        "font-size: 15px; font-weight: bold; color: #1A3A6B;");
    QLabel *headerSub = new QLabel("Patterns · Prévisions · IA");
    headerSub->setStyleSheet("font-size: 11px; color: #6B89C0;");
    headerText->addWidget(headerTitle);
    headerText->addWidget(headerSub);
    headerText->setSpacing(2);

    headerL->addWidget(headerIcon);
    headerL->addLayout(headerText);
    headerL->addStretch();
    root->addWidget(headerFrame);

    // ── Section : Taux par saison ─────────────────────────────
    QLabel *secSaison = new QLabel("TAUX D'OCCUPATION PAR SAISON");
    secSaison->setStyleSheet(
        "font-size: 10px; font-weight: bold; color: #90A4AE; letter-spacing: 1px;");
    root->addWidget(secSaison);

    struct Saison {
        QString nom, emoji, couleur, bg, border;
        int     taux;
    };
    QList<Saison> saisons = {
                             {"Printemps", "🌸", "#2E7D32", "#E8F5E9", "#A5D6A7", tauxPrintemps},
                             {"Été",       "☀️",  "#B71C1C", "#FFEBEE", "#FFCDD2", tauxEte      },
                             {"Automne",   "🍂", "#E65100", "#FFF3E0", "#FFCC80", tauxAutomne  },
                             {"Hiver",     "❄️",  "#0D47A1", "#E3F2FD", "#90CAF9", tauxHiver    },
                             };

    QHBoxLayout *saisonRow = new QHBoxLayout();
    saisonRow->setSpacing(10);

    for (const Saison &s : saisons) {
        QFrame *card = new QFrame();
        card->setStyleSheet(QString(
                                "QFrame { background: %1; border-radius: 12px; "
                                "border: 1.5px solid %2; }").arg(s.bg, s.border));
        card->setFixedHeight(95);

        QVBoxLayout *cl = new QVBoxLayout(card);
        cl->setContentsMargins(12, 10, 12, 10);
        cl->setSpacing(4);
        cl->setAlignment(Qt::AlignCenter);

        QLabel *emoji = new QLabel(s.emoji);
        emoji->setAlignment(Qt::AlignCenter);
        emoji->setStyleSheet("font-size: 20px;");

        QLabel *nomLbl = new QLabel(s.nom);
        nomLbl->setAlignment(Qt::AlignCenter);
        nomLbl->setStyleSheet(QString(
                                  "font-size: 11px; font-weight: bold; color: %1;").arg(s.couleur));

        QLabel *tauxLbl = new QLabel(QString::number(s.taux) + "%");
        tauxLbl->setAlignment(Qt::AlignCenter);
        tauxLbl->setStyleSheet(QString(
                                   "font-size: 22px; font-weight: bold; color: %1;").arg(s.couleur));

        cl->addWidget(emoji);
        cl->addWidget(nomLbl);
        cl->addWidget(tauxLbl);
        saisonRow->addWidget(card);
    }
    root->addLayout(saisonRow);

    // ── Section : Détail par mois (barres) ───────────────────
    QLabel *secMois = new QLabel("DÉTAIL PAR MOIS");
    secMois->setStyleSheet(
        "font-size: 10px; font-weight: bold; color: #90A4AE; letter-spacing: 1px;");
    root->addWidget(secMois);

    QFrame *chartFrame = new QFrame();
    chartFrame->setStyleSheet(
        "QFrame { background: white; border-radius: 12px; border: 1px solid #E0E0E0; }");
    chartFrame->setFixedHeight(130);

    QHBoxLayout *chartL = new QHBoxLayout(chartFrame);
    chartL->setContentsMargins(12, 14, 12, 8);
    chartL->setSpacing(4);

    QStringList moisNoms = {"Jan","Fév","Mar","Avr","Mai","Jun",
                            "Jul","Aoû","Sep","Oct","Nov","Déc"};

    // couleur selon saison
    auto couleurMois = [](int m) -> QString {
        if (m >= 3  && m <= 5)  return "#A5D6A7";   // printemps
        if (m >= 6  && m <= 8)  return "#FFCDD2";   // été
        if (m >= 9  && m <= 11) return "#FFCC80";   // automne
        return "#90CAF9";                             // hiver
    };

    for (int m = 1; m <= 12; m++) {
        int taux = tauxParMois.value(m, 0);

        QVBoxLayout *barCol = new QVBoxLayout();
        barCol->setSpacing(3);
        barCol->setAlignment(Qt::AlignBottom | Qt::AlignHCenter);

        // Barre
        QFrame *barContainer = new QFrame();
        barContainer->setFixedWidth(32);
        QVBoxLayout *barContL = new QVBoxLayout(barContainer);
        barContL->setContentsMargins(0, 0, 0, 0);
        barContL->setSpacing(0);
        barContL->addStretch();

        QFrame *bar = new QFrame();
        bar->setFixedWidth(28);
        int barH = qMax(4, taux * 65 / 100);   // max 65px
        bar->setFixedHeight(barH);
        QString col = couleurMois(m);
        // Plus foncé si taux > 80
        if (taux > 80) col = (m >= 6 && m <= 8) ? "#EF5350" :
                      (m >= 3 && m <= 5)  ? "#4CAF50" :
                      (m >= 9 && m <= 11) ? "#FF9800" : "#42A5F5";
        bar->setStyleSheet(QString(
                               "QFrame { background: %1; border-radius: 4px 4px 0 0; border: none; }")
                               .arg(col));
        bar->setToolTip(moisNoms[m-1] + " : " + QString::number(taux) + "%");

        barContL->addWidget(bar, 0, Qt::AlignHCenter);

        // Label mois
        QLabel *moisLbl = new QLabel(moisNoms[m-1]);
        moisLbl->setAlignment(Qt::AlignCenter);
        moisLbl->setStyleSheet("font-size: 9px; color: #90A4AE;");
        moisLbl->setFixedWidth(32);

        barCol->addWidget(barContainer, 1);
        barCol->addWidget(moisLbl);
        chartL->addLayout(barCol);
    }
    root->addWidget(chartFrame);

    // ── Section : Prévisions & Alertes ────────────────────────
    QLabel *secAlertes = new QLabel("PRÉVISIONS & ALERTES");
    secAlertes->setStyleSheet(
        "font-size: 10px; font-weight: bold; color: #90A4AE; letter-spacing: 1px;");
    root->addWidget(secAlertes);

    QScrollArea *scrollAlertes = new QScrollArea();
    scrollAlertes->setWidgetResizable(true);
    scrollAlertes->setFrameShape(QFrame::NoFrame);
    scrollAlertes->setMaximumHeight(160);
    scrollAlertes->setStyleSheet(
        "QScrollArea { background: transparent; border: none; }"
        "QScrollBar:vertical { background: #ECEFF1; width: 6px; border-radius: 3px; }"
        "QScrollBar::handle:vertical { background: #B0BEC5; border-radius: 3px; }");

    QWidget *alertWidget = new QWidget();
    alertWidget->setStyleSheet("background: transparent;");
    QVBoxLayout *alertL = new QVBoxLayout(alertWidget);
    alertL->setContentsMargins(0, 0, 4, 0);
    alertL->setSpacing(6);

    // Générer alertes pour chaque mois > 70%
    bool hasAlerte = false;
    for (int m = 1; m <= 12; m++) {
        int taux = tauxParMois.value(m, 0);
        if (taux < 70) continue;
        hasAlerte = true;

        // Calculer nb emplacements supplémentaires suggérés
        int empSup = 0;
        if      (taux >= 90) empSup = qMax(1, totalEmplacements * 20 / 100);
        else if (taux >= 80) empSup = qMax(1, totalEmplacements * 10 / 100);
        else                 empSup = qMax(1, totalEmplacements *  5 / 100);

        QString niveau, bg, border, emoji;
        if (taux >= 90) {
            niveau = "CRITIQUE"; bg = "#FFEBEE"; border = "#EF9A9A"; emoji = "🔴";
        } else if (taux >= 80) {
            niveau = "ÉLEVÉ";    bg = "#FFF3E0"; border = "#FFCC80"; emoji = "🟠";
        } else {
            niveau = "MODÉRÉ";   bg = "#FFFDE7"; border = "#FFF176"; emoji = "🟡";
        }

        QFrame *alertCard = new QFrame();
        alertCard->setStyleSheet(QString(
                                     "QFrame { background: %1; border-radius: 10px; "
                                     "border: 1px solid %2; }").arg(bg, border));

        QHBoxLayout *aL = new QHBoxLayout(alertCard);
        aL->setContentsMargins(12, 8, 12, 8);
        aL->setSpacing(10);

        QLabel *aIcon = new QLabel(emoji);
        aIcon->setStyleSheet("font-size: 16px;");
        aIcon->setFixedWidth(22);

        QVBoxLayout *aText = new QVBoxLayout();
        aText->setSpacing(1);
        QLabel *aTitle = new QLabel(
            moisNoms[m-1] + " : taux historique " + QString::number(taux) + "%");
        aTitle->setStyleSheet(
            "font-size: 12px; font-weight: bold; color: #263238;");
        QLabel *aSub = new QLabel(
            taux >= 80
                ? "Préparer " + QString::number(empSup) + " emplacement(s) supplémentaire(s)"
                : "Surveiller — proche du seuil critique");
        aSub->setStyleSheet("font-size: 10px; color: #78909C;");
        aText->addWidget(aTitle);
        aText->addWidget(aSub);

        QLabel *aBadge = new QLabel(
            taux >= 80
                ? "+" + QString::number(empSup) + " emp."
                : "surveillance");
        aBadge->setStyleSheet(QString(
                                  "font-size: 10px; font-weight: bold; color: %1; "
                                  "background: transparent; border: none;")
                                  .arg(taux >= 90 ? "#C62828" : taux >= 80 ? "#E65100" : "#F9A825"));

        aL->addWidget(aIcon);
        aL->addLayout(aText);
        aL->addStretch();
        aL->addWidget(aBadge);
        alertL->addWidget(alertCard);
    }

    if (!hasAlerte) {
        QLabel *ok = new QLabel("✅  Aucune période critique détectée — taux d'occupation sains.");
        ok->setStyleSheet(
            "font-size: 12px; color: #2E7D32; padding: 12px; "
            "background: #E8F5E9; border-radius: 8px;");
        alertL->addWidget(ok);
    }

    alertL->addStretch();
    scrollAlertes->setWidget(alertWidget);
    root->addWidget(scrollAlertes);

    // ── Bouton Fermer ─────────────────────────────────────────
    QHBoxLayout *btnRow = new QHBoxLayout();
    btnRow->addStretch();
    QPushButton *btnFermer = new QPushButton("Fermer");
    btnFermer->setFixedHeight(36);
    btnFermer->setStyleSheet(
        "QPushButton { background: #1A3A6B; color: white; border-radius: 8px; "
        "padding: 0 28px; font-size: 13px; font-weight: bold; border: none; }"
        "QPushButton:hover { background: #0D47A1; }");
    QObject::connect(btnFermer, &QPushButton::clicked, dlg, &QDialog::close);
    btnRow->addWidget(btnFermer);
    root->addLayout(btnRow);

    dlg->exec();
}
