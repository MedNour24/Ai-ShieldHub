#include "fishingport.h"
#include "ui_fishingport.h"
#include <QApplication>
#include <QFileDialog>
#include <QHeaderView>
#include <QMessageBox>
#include <QPainter>
#include <QPixmap>
#include <QRegularExpression>
#include <QTextStream>
#include <QSqlQuery>
#include <QSqlError>
#include <QScrollArea>
#include <QGraphicsDropShadowEffect>
#include <QTimer>
#include <QFrame>
#include <QStandardItemModel>
#include <QItemSelectionModel>
#include <QtCharts/QChartView>
#include <QtCharts/QPieSeries>
#include <QtCharts/QPieSlice>
#include <QtCharts/QBarSeries>
#include <QtCharts/QBarSet>
#include <QtCharts/QBarCategoryAxis>
#include <QtCharts/QValueAxis>
#include <QtCharts/QChart>
#include <QtCharts/QLineSeries>
#include <QtCharts/QAbstractBarSeries>
#include "bateau.h"
#include "emplacement.h"

fishingport::fishingport(QWidget *parent)
    : QMainWindow(parent), ui(new Ui::fishingport) {
  ui->setupUi(this);

  model = new QSqlQueryModel(this);
  networkManager = new QNetworkAccessManager(this);

  ui->tableBateaux->setModel(model);
  ui->tableBateaux->horizontalHeader()->setSectionResizeMode(
      QHeaderView::Stretch);
  ui->tableBateaux->horizontalHeader()->setDefaultAlignment(Qt::AlignCenter);
  ui->tableBateaux->setStyleSheet("QTableView::item { text-align: center; }");
  ui->tableBateaux->verticalHeader()->setVisible(false);
  ui->tableBateaux->setSelectionBehavior(QAbstractItemView::SelectRows);
  ui->tableBateaux->setSelectionMode(QAbstractItemView::SingleSelection);
  ui->tableBateaux->setAlternatingRowColors(true);
  ui->tableBateaux->setShowGrid(false);

  connect(ui->lineEdit, &QLineEdit::textChanged, this,
          &fishingport::applyFilters);
  connect(ui->comboBox, QOverload<int>::of(&QComboBox::currentIndexChanged),
          this, &fishingport::applyFilters);
  connect(ui->comboBoxType, QOverload<int>::of(&QComboBox::currentIndexChanged),
          this, &fishingport::applyFilters);
  connect(ui->comboTri, QOverload<int>::of(&QComboBox::currentIndexChanged),
          this, &fishingport::applyFilters);

  loadBateaux();

  ui->stackedWidget->setCurrentIndex(0);
  ui->stackedWidget_2->setCurrentIndex(0);

  connect(ui->btnNavEmplacements, &QPushButton::clicked,
          this, &fishingport::on_btnNavEmplacements_clicked);

  QString style = R"(
QMainWindow { background-color: #E5E7EB; }
QWidget { font-family: "Segoe UI", "Inter", Arial, sans-serif; font-size: 12px; color: #25333A; }

/* ======== HEADER BAR & SIDEBAR (Adapted to Light Theme) ======== */
QWidget#headerBar {
    background: qlineargradient(x1:0, y1:0, x2:1, y2:0, stop:0 #0288D1, stop:1 #4FC3F7);
    border-bottom: 2px solid #0056b3;
}
QLabel#lblLogo { font-size: 28px; color: #E0F7FA; padding-left: 4px; }
QLabel#lblAppName { font-size: 20px; font-weight: 700; color: #FFFFFF; letter-spacing: 1px; padding-left: 10px; }
QLabel#lblPortStatus { font-size: 12px; font-weight: 600; color: #2E7D32; padding: 6px 14px; border: 1px solid rgba(46,125,50,0.3); border-radius: 20px; background-color: #C8E6C9; }
QLabel#lblDateTime { font-size: 12px; color: #E1F5FE; padding-left: 18px; }

QWidget#sidebar { background-color: #FFFFFF; border-right: 2px solid #81D4FA; }
QLabel#lblMenuTitle { font-size: 9px; font-weight: 700; color: #0288D1; letter-spacing: 2px; padding: 4px 6px 8px 6px; }
QWidget#sidebar QPushButton { background-color: transparent; color: #1A237E; border: none; border-radius: 10px; padding: 11px 16px; text-align: left; font-size: 12px; font-weight: 500; min-height: 38px; }
QWidget#sidebar QPushButton:hover { background-color: #E1F5FE; color: #0288D1; }
QWidget#sidebar QPushButton:checked { background: #B3E5FC; color: #0D47A1; border-left: 4px solid #0288D1; font-weight: 700; border-radius: 0 10px 10px 0; }

/* -------- USER BASE CONTROLS -------- */
QGroupBox { background: qlineargradient(x1:0, y1:0, x2:0, y2:1, stop:0 #E0F7FA, stop:1 #B2EBF2); border: 3px solid #4DD0E1; border-radius: 15px; margin-top: 20px; padding: 20px; font-weight: bold; font-size: 14px; }
QGroupBox::title { subcontrol-origin: margin; subcontrol-position: top left; padding: 8px 15px; background-color: #00ACC1; color: white; border-radius: 8px; font-size: 15px; font-weight: bold; margin-left: 15px; }
QLabel { color: #0D47A1; font-weight: 600; font-size: 13px; }
QLineEdit, QSpinBox, QDoubleSpinBox, QDateEdit, QDateTimeEdit { background-color: #FFFFFF; color: #1A237E; border: 2px solid #81D4FA; border-radius: 10px; padding: 10px 12px; min-height: 32px; font-size: 13px; }
QLineEdit:focus, QSpinBox:focus { border: 3px solid #FFB74D; background-color: #FFF9E6; }
QLineEdit:hover, QSpinBox:hover { border: 2px solid #4FC3F7; background-color: #F0FAFF; }
QLineEdit:read-only { background-color: #ECEFF1; border: 2px solid #B0BEC5; color: #546E7A; }

QComboBox { background-color: #FFFFFF; color: #1A237E; border: 2px solid #81D4FA; border-radius: 10px; padding: 10px 12px; min-height: 32px; font-size: 13px; }
QComboBox:hover { border: 2px solid #4FC3F7; background-color: #F0FAFF; }
QComboBox:focus { border: 3px solid #FFB74D; }
QComboBox::drop-down { border-left: 2px solid #81D4FA; width: 25px; border-radius: 8px; }
QComboBox::down-arrow { image: none; border: 5px solid transparent; border-top: 6px solid #0288D1; margin-right: 5px; }
QComboBox QAbstractItemView { background: #FFFFFF; selection-background-color: #81D4FA; selection-color: #0D47A1; border: 2px solid #4FC3F7; border-radius: 8px; }

QRadioButton { color: #0D47A1; font-size: 14px; font-weight: 600; spacing: 8px; }
QRadioButton::indicator { width: 20px; height: 20px; border: 3px solid #81D4FA; border-radius: 12px; background: white; }
QRadioButton::indicator:checked { background: qradialgradient(cx:0.5, cy:0.5, radius:0.5, fx:0.5, fy:0.5, stop:0 #0288D1, stop:0.6 #0288D1, stop:0.7 white); border: 3px solid #0288D1; }

QPushButton { background: qlineargradient(x1:0, y1:0, x2:0, y2:1, stop:0 #E3F2FD, stop:1 #BBDEFB); color: #0D47A1; border: 2px solid #64B5F6; border-radius: 12px; padding: 12px 20px; font-weight: bold; font-size: 13px; min-height: 38px; }
QPushButton:hover { background: qlineargradient(x1:0, y1:0, x2:0, y2:1, stop:0 #BBDEFB, stop:1 #90CAF9); border: 3px solid #42A5F5; }
QPushButton:pressed { background: #81D4FA; }

QTableWidget, QTableView { background-color: #FFFFFF; border-radius: 12px; border: 3px solid #B3E5FC; gridline-color: #E1F5FE; alternate-background-color: #F1F8E9; color: #0D47A1; selection-background-color: #81D4FA; }
QTableWidget::item, QTableView::item { color: #0D47A1; padding: 10px; border-bottom: 1px solid #E1F5FE; }
QTableWidget::item:selected, QTableView::item:selected { background-color: #4FC3F7; color: white; font-weight: bold; }
QHeaderView::section { background: qlineargradient(x1:0, y1:0, x2:0, y2:1, stop:0 #0288D1, stop:1 #0277BD); color: white; font-weight: bold; padding: 12px; border: none; border-right: 1px solid #4FC3F7; font-size: 13px; }
QHeaderView::section:first { border-radius: 10px 0 0 0; }
QHeaderView::section:last  { border-right: none; border-radius: 0 10px 0 0; }

QScrollBar:vertical { background: #E3F2FD; width: 12px; border-radius: 6px; }
QScrollBar::handle:vertical { background: #81D4FA; min-height: 30px; border-radius: 6px; }

/* ======== STATS PAGE LABELS (Preserved from Base) ======== */
QLabel#lblTotal { background-color: #E3F2FD; color: #0D47A1; font-size: 14px; font-weight: bold; padding: 5px 10px; border-radius: 8px; }
QLabel#lblLibres { background-color: #C8E6C9; color: #2E7D32; font-size: 14px; font-weight: bold; padding: 5px 10px; border-radius: 8px; }
QLabel#lblOccupes { background-color: #FFCDD2; color: #C62828; font-size: 14px; font-weight: bold; padding: 5px 10px; border-radius: 8px; }
QLabel#lblMaintenance{ background-color: #FFF9C4; color: #F57F17; font-size: 14px; font-weight: bold; padding: 5px 10px; border-radius: 8px; }

/* ======== CUSTOM BUTTONS (Adapted) ======== */
QPushButton#btnAjouter, QPushButton#btnEnregistrer { background: qlineargradient(x1:0, y1:0, x2:1, y2:0, stop:0 #4FC3F7, stop:1 #0288D1); color: #FFFFFF; border: none; }
QPushButton#btnDelete { background: #FFCDD2; color: #C62828; border: 2px solid #EF9A9A; }
QPushButton#btnDelete:hover { background: #EF5350; color: white; }

/* -------- PREDICTION PAGE (Adapted to Light Theme) -------- */
QLabel#lblPredictionTitle { font-size: 22px; font-weight: 700; color: #0D47A1; letter-spacing: 0.5px; }
QLabel#lblPredictionSubtitle { font-size: 13px; color: #0288D1; }
QFrame#cardPredictionInfo, QFrame#cardPredictionResult { background-color: #FFFFFF; border: 2px solid #81D4FA; border-radius: 16px; }
QLabel#lblPredInfoTitle, QLabel#lblPredResultTitle { font-size: 15px; font-weight: 700; color: #0288D1; padding-bottom: 6px; }
QFrame#cardPredictionInfo QLabel { font-size: 13px; color: #1A237E; padding: 2px 0; }
QPushButton#btnLaunchPrediction { background: qlineargradient(x1:0, y1:0, x2:1, y2:0, stop:0 #7B2FF7, stop:1 #00B4D8); color: #FFFFFF; border: none; font-weight: 700; }
QPushButton#btnPrediction { background: #E1BEE7; color: #6A1B9A; border: 2px solid #CE93D8; }
QPushButton#btnPrediction:hover { background: #CE93D8; color: #FFFFFF; }
)";
  qApp->setStyleSheet(style);
}

fishingport::~fishingport() { delete ui; }

void fishingport::loadBateaux() {
  Bateau::loadTable(model);

  ui->tableBateaux->setModel(model);
  ui->tableBateaux->horizontalHeader()->setSectionResizeMode(
      QHeaderView::Stretch);
  ui->tableBateaux->horizontalHeader()->setDefaultAlignment(Qt::AlignCenter);
  ui->tableBateaux->verticalHeader()->setVisible(false);

  int enMer = Bateau::countStatus("En mer");
  int aQuai = Bateau::countStatus("A quai");
  int maint = Bateau::countStatus("En maintenance");
  int total = enMer + aQuai + maint;

  ui->lblStatEnMer->setText("En mer : " + QString::number(enMer));
  ui->lblStatAQuai->setText("A quai : " + QString::number(aQuai));
  ui->lblStatMaintenance->setText("En maintenance : " + QString::number(maint));
  ui->lblStatTotal->setText("Total : " + QString::number(total));
}

void fishingport::applyFilters() {
  QString search = ui->lineEdit->text().trimmed();
  QString status = ui->comboBox->currentText();
  QString type = ui->comboBoxType->currentText();
  int triIndex = ui->comboTri->currentIndex();

  Bateau::filterTable(model, search, status, type, triIndex);

  ui->tableBateaux->horizontalHeader()->setSectionResizeMode(
      QHeaderView::Stretch);
  ui->tableBateaux->horizontalHeader()->setDefaultAlignment(Qt::AlignCenter);
  ui->tableBateaux->verticalHeader()->setVisible(false);
}

void fishingport::loadStatistiques() {
  int enMer = Bateau::countStatus("En mer");
  int aQuai = Bateau::countStatus("A quai");
  int maint = Bateau::countStatus("En maintenance");
  int total = enMer + aQuai + maint;

  // Cards
  ui->lblCardMerCount->setText(QString::number(enMer));
  ui->lblCardQuaiCount->setText(QString::number(aQuai));
  ui->lblCardMaintCount->setText(QString::number(maint));
  ui->lblCardTotalCount->setText(QString::number(total));

  // Sidebar
  ui->lblStatEnMer->setText("En mer : " + QString::number(enMer));
  ui->lblStatAQuai->setText("A quai : " + QString::number(aQuai));
  ui->lblStatMaintenance->setText("En maintenance : " + QString::number(maint));
  ui->lblStatTotal->setText("Total : " + QString::number(total));

  // Detail table
  QTableWidget *t = ui->tableStats;
  t->setRowCount(4);
  t->setColumnCount(2);
  t->setHorizontalHeaderItem(0, new QTableWidgetItem("Statut"));
  t->setHorizontalHeaderItem(1, new QTableWidgetItem("Nombre de bateaux"));
  t->horizontalHeader()->setSectionResizeMode(QHeaderView::Stretch);
  t->verticalHeader()->setVisible(false);

  auto addRow = [&](int row, const QString &label, int count) {
    t->setItem(row, 0, new QTableWidgetItem(label));
    QTableWidgetItem *numItem = new QTableWidgetItem(QString::number(count));
    numItem->setTextAlignment(Qt::AlignCenter);
    t->setItem(row, 1, numItem);
  };

  addRow(0, "🌊  En mer", enMer);
  addRow(1, "⚓  A quai", aQuai);
  addRow(2, "🔧  En maintenance", maint);
  addRow(3, "🚢  Total", total);

  // --- Draw Bar Chart ---
  int width = 550;
  int height = 250;
  QPixmap pixmap(width, height);
  pixmap.fill(Qt::transparent);

  QPainter painter(&pixmap);
  painter.setRenderHint(QPainter::Antialiasing);

  int maxVal = qMax(qMax(enMer, aQuai), maint);
  if (maxVal == 0)
    maxVal = 1; // avoid div by zero

  int marginX = 40;
  int marginY = 30;
  int chartWidth = width - marginX * 2;
  int chartHeight = height - marginY * 2;

  // Draw axes
  painter.setPen(QPen(QColor(0xFF7C9CBF), 2));
  painter.drawLine(marginX, height - marginY, width - marginX,
                   height - marginY);                            // X axis
  painter.drawLine(marginX, marginY, marginX, height - marginY); // Y axis

  // Draw bars
  QStringList labels = {"En mer", "A quai", "Maintenance"};
  QList<int> values = {enMer, aQuai, maint};
  QList<QColor> colors = {QColor(0xFF48CAE4), QColor(0xFF2DC653),
                          QColor(0xFFF4A825)};

  int numBars = values.size();
  int barSpacing = 40;
  int totalSpacing = (numBars + 1) * barSpacing;
  int barWidth = (chartWidth - totalSpacing) / numBars;

  QFont font = painter.font();
  font.setPointSize(9);
  font.setBold(true);
  painter.setFont(font);

  for (int i = 0; i < numBars; ++i) {
    int barHeight = (values[i] * chartHeight) / maxVal;
    int x = marginX + barSpacing + i * (barWidth + barSpacing);
    int y = height - marginY - barHeight;

    painter.setBrush(colors[i]);
    painter.setPen(Qt::NoPen);
    painter.drawRoundedRect(x, y, barWidth, barHeight, 4, 4);

    // Value label on top (using standard Qt alignment)
    painter.setPen(QColor(0xFFE8F4FD));
    painter.drawText(x, y - 20, barWidth, 20, Qt::AlignCenter,
                     QString::number(values[i]));

    // Axis label below
    painter.setPen(QColor(0xFF7C9CBF));
    painter.drawText(x - 10, height - marginY + 5, barWidth + 20, 20,
                     Qt::AlignCenter, labels[i]);
  }

  painter.end();
  ui->chartLabel->setPixmap(pixmap);
}

void fishingport::on_pushButton_clicked() {
  QString path = QFileDialog::getSaveFileName(
      this, "Exporter CSV", "bateaux.csv", "CSV Files (*.csv)");
  if (path.isEmpty())
    return;

  QFile file(path);
  if (!file.open(QIODevice::WriteOnly | QIODevice::Text)) {
    QMessageBox::critical(this, "Erreur", "Impossible d'ouvrir le fichier.");
    return;
  }

  QTextStream out(&file);
  // BOM for Excel UTF-8
  out << "\xEF\xBB\xBF";

  // Header
  out << "Carte Grise;Nom;Statut;Date Fabrication;Type\n";

  // Rows from model
  int rows = model->rowCount();
  int cols = model->columnCount();
  for (int r = 0; r < rows; ++r) {
    QStringList fields;
    for (int c = 0; c < cols; ++c) {
      QString val = model->data(model->index(r, c)).toString();
      // Escape semicolons / quotes
      val.replace("\"", "\"\"");
      if (val.contains(';') || val.contains('"') || val.contains('\n'))
        val = "\"" + val + "\"";
      fields << val;
    }
    out << fields.join(';') << "\n";
  }

  file.close();
  QMessageBox::information(
      this, "Export CSV",
      QString("Fichier exporté avec succès !\n%1 enregistrement(s).")
          .arg(rows));
}

void fishingport::on_btnNavBateaux_clicked() {
    ui->btnNavBateaux->setChecked(true);
    loadBateaux();
    ui->stackedWidget->setCurrentIndex(0);
    ui->stackedWidget_2->setCurrentIndex(0);
}

void fishingport::on_btnRefreshStats_clicked() { loadStatistiques(); }

void fishingport::on_pushButton_2_clicked() {
    ui->lineEdit->clear();
    ui->comboBox->setCurrentIndex(0);
    ui->comboBoxType->setCurrentIndex(0);
    ui->comboTri->setCurrentIndex(0);
    loadBateaux();
}

void fishingport::on_btnAjouter_clicked() {
    m_editMode = false;
    ui->lineEditNum->clear();
    ui->lineEditNom->clear();
    ui->comboStatus->setCurrentIndex(0);
    ui->comboType->setCurrentIndex(0);
    ui->dateEdit->setDate(QDate::currentDate());
    ui->stackedWidget_2->setCurrentIndex(1);
}

void fishingport::on_tableBateaux_clicked(const QModelIndex &index) {
    int row = index.row();
    m_originalNum = model->data(model->index(row, 0)).toString();
    ui->lineEditNum->setText(m_originalNum);
    ui->lineEditNom->setText(model->data(model->index(row, 1)).toString());
    ui->comboStatus->setCurrentText(model->data(model->index(row, 2)).toString());
    ui->dateEdit->setDate(model->data(model->index(row, 3)).toDate());
    ui->comboType->setCurrentText(model->data(model->index(row, 4)).toString());
}

void fishingport::on_btnEnregistrer_clicked() {
    QString num = ui->lineEditNum->text().trimmed();
    QString nom = ui->lineEditNom->text().trimmed();
    QDate date = ui->dateEdit->date();

    QRegularExpression re("^\\d{8}$");
    if (!re.match(num).hasMatch()) {
        QMessageBox::warning(
            this, "Erreur de saisie",
            "Le numéro de carte grise doit contenir exactement 8 chiffres.");
        ui->lineEditNum->setFocus();
        return;
    }
    if (nom.isEmpty()) {
        QMessageBox::warning(this, "Erreur de saisie",
                             "Le nom du bateau ne peut pas être vide.");
        ui->lineEditNom->setFocus();
        return;
    }
    if (date > QDate::currentDate()) {
        QMessageBox::warning(
            this, "Erreur de saisie",
            "La date de fabrication ne peut pas être dans le futur.");
        ui->dateEdit->setFocus();
        return;
    }

    Bateau b(num, nom, ui->comboStatus->currentText(), date, ui->comboType->currentText());

    if (m_editMode) {
        if (!b.modifier(m_originalNum)) {
            QMessageBox::critical(this, "Error", "Erreur lors de la modification.");
            return;
        }
        QMessageBox::information(this, "Updated", "Bateau mis à jour avec succès.");
        m_editMode = false;
        m_originalNum.clear();
    } else {
        if (Bateau::existe(num)) {
            QMessageBox::warning(this, "Erreur de saisie", "Ce bateau existe déjà (numéro de carte grise existant).");
            ui->lineEditNum->setFocus();
            return;
        }

        if (!b.ajouter()) {
            QMessageBox::critical(this, "Error", "Erreur lors de l'ajout.");
            return;
        }
        QMessageBox::information(this, "Success", "Bateau ajouté avec succès.");
    }

    loadBateaux();
    ui->stackedWidget_2->setCurrentIndex(0);
}

void fishingport::on_btnModify_clicked() {
    if (ui->lineEditNum->text().isEmpty()) {
        QMessageBox::warning(
            this, "Warning",
            "Veuillez sélectionner un bateau dans le tableau d'abord.");
        return;
    }
    m_editMode = true;
    ui->stackedWidget_2->setCurrentIndex(1);
}

void fishingport::on_btnDelete_clicked() {
    if (ui->lineEditNum->text().isEmpty()) {
        QMessageBox::warning(
            this, "Warning",
            "Veuillez sélectionner un bateau dans le tableau d'abord.");
        return;
    }

    if (!Bateau::supprimer(ui->lineEditNum->text())) {
        QMessageBox::critical(this, "Error", "Erreur lors de la suppression.");
        return;
    }
    QMessageBox::information(this, "Deleted", "Bateau supprimé avec succès.");
    ui->lineEditNum->clear();
    loadBateaux();
}

void fishingport::on_btnConsulter_clicked() {
    if (ui->lineEditNum->text().isEmpty()) {
        QMessageBox::warning(
            this, "Consulter",
            "Veuillez sélectionner un bateau dans le tableau d'abord.");
        return;
    }

    QString details = "<b>Détails du Bateau</b><br><br>"
                      "<table cellspacing='6'>"
                      "<tr><td><b>Carte Grise :</b></td><td>" +
                      ui->lineEditNum->text() +
                      "</td></tr>"
                      "<tr><td><b>Nom :</b></td><td>" +
                      ui->lineEditNom->text() +
                      "</td></tr>"
                      "<tr><td><b>Statut :</b></td><td>" +
                      ui->comboStatus->currentText() +
                      "</td></tr>"
                      "<tr><td><b>Type :</b></td><td>" +
                      ui->comboType->currentText() +
                      "</td></tr>"
                      "<tr><td><b>Date Fabrication :</b></td><td>" +
                      ui->dateEdit->date().toString("dd/MM/yyyy") +
                      "</td></tr>"
                      "</table>";

    QMessageBox msg(this);
    msg.setWindowTitle("Consulter — Fiche Bateau");
    msg.setTextFormat(Qt::RichText);
    msg.setText(details);
    msg.setIcon(QMessageBox::Information);
    msg.exec();
}

void fishingport::on_btnAnnuler_clicked() {
    m_editMode = false;
    m_originalNum.clear();
    ui->stackedWidget_2->setCurrentIndex(0);
}

void fishingport::on_btnActionStats_clicked() {
    ui->btnNavBateaux->setChecked(false);
    loadStatistiques();
    ui->stackedWidget_2->setCurrentIndex(2);
}

void fishingport::on_btnRetour_clicked() {
    // Acts identically to clicking the Bateaux navigation button
    on_btnNavBateaux_clicked();
}

void fishingport::on_btnSendChat_clicked() {
    QString question = ui->txtChatInput->text().trimmed();
    if (question.isEmpty())
        return;

    ui->txtChatHistory->append("<b>Vous:</b> " + question);
    ui->txtChatInput->clear();

    sendToOllama(question);
}

void fishingport::sendToOllama(const QString &question) {
    // 1. Build the Context from the Database
    QString context = "Voici la base de données actuelle des bateaux du port:\n";
    QSqlQuery query(
        "SELECT numero_carte_grise, nomB, statusB, id_emplacement FROM bateau");
    while (query.next()) {
        context +=
            "- Bateau '" + query.value("nomB").toString() +
            "' (Carte Grise: " + query.value("numero_carte_grise").toString() +
            "), Statut: " + query.value("statusB").toString() +
            ", Emplacement ID: " + query.value("id_emplacement").toString() + "\n";
    }

    // 2. Build the System Prompt
    QString systemPrompt = "Tu es l'assistant intelligent du Port de Pêche. "
                           "Tu dois répondre brièvement et de manière concise "
                           "aux questions de l'utilisateur "
                           "en te basant UNIQUEMENT sur ces données en temps "
                           "réel. Ne pose pas de questions toi-même:\n\n" +
                           context;

    // 3. Prepare JSON for Ollama REST API
    QJsonObject json;
    json["model"] = "mistral";
    json["system"] = systemPrompt;
    json["prompt"] = question;
    json["stream"] = true; // CHANGED to true for streaming

    QJsonDocument doc(json);
    QByteArray data = doc.toJson();

    QUrl url("http://localhost:11434/api/generate");
    QNetworkRequest request(url);
    request.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");

    // 4. Send Request
    ui->txtChatHistory->append("<b>Assistant:</b> ");
    QNetworkReply *reply = networkManager->post(request, data);

    // Handle streaming data as it arrives
    connect(reply, &QNetworkReply::readyRead, this, [this, reply]() {
        while (reply->canReadLine()) {
            QByteArray line = reply->readLine().trimmed();
            if (line.isEmpty())
                continue;

            QJsonDocument streamDoc = QJsonDocument::fromJson(line);
            QJsonObject streamObj = streamDoc.object();
            if (streamObj.contains("response")) {
                QString chunk = streamObj["response"].toString();

                // Insert word-by-word at the very end of the chat history
                // without triggering a new paragraph line break each time
                QTextCursor cursor = ui->txtChatHistory->textCursor();
                cursor.movePosition(QTextCursor::End);
                ui->txtChatHistory->setTextCursor(cursor);
                ui->txtChatHistory->insertPlainText(chunk);
            }
        }
    });

    // Handle network completion
    connect(reply, &QNetworkReply::finished, this, [this, reply]() {
        if (reply->error() != QNetworkReply::NoError) {
            ui->txtChatHistory->append(
                "<br><b style='color:red;'>Erreur:</b> Impossible de joindre Ollama. "
                "Assurez-vous qu'il est en cours d'exécution (ollama run mistral).");
        }

        // Add empty line for spacing between messages
        ui->txtChatHistory->append("<br>");
        reply->deleteLater();

        // Auto-scroll to bottom just in case
        QTextCursor cursor = ui->txtChatHistory->textCursor();
        cursor.movePosition(QTextCursor::End);
        ui->txtChatHistory->setTextCursor(cursor);
    });
}

void fishingport::on_btnPrediction_clicked() {
    if (ui->lineEditNum->text().isEmpty()) {
        QMessageBox::warning(
            this, "Prédiction IA",
            "Veuillez sélectionner un bateau dans le tableau d'abord.");
        return;
    }

    QString num = ui->lineEditNum->text().trimmed();
    QString nom = ui->lineEditNom->text().trimmed();
    QString type = ui->comboType->currentText();
    QDate dateFab = ui->dateEdit->date();

    // Count sorties for this boat via the 'avoir' junction table
    int nbSorties = Bateau::countSorties(num);

    // Calculate age
    QDate today = QDate::currentDate();
    int ageYears = dateFab.daysTo(today) / 365;
    int ageMonths = (dateFab.daysTo(today) % 365) / 30;

    // Populate the prediction page labels
    ui->lblPredBoatName->setText("Nom : " + nom);
    ui->lblPredBoatType->setText("Type : " + type);
    ui->lblPredBoatDate->setText("Date de fabrication : " +
                                 dateFab.toString("dd/MM/yyyy"));
    ui->lblPredBoatAge->setText(
        QString("Âge : %1 ans et %2 mois").arg(ageYears).arg(ageMonths));
    ui->lblPredBoatSorties->setText("Nombre de sorties : " +
                                    QString::number(nbSorties));

    // Clear previous result
    ui->txtPredictionResult->clear();

    // Navigate to prediction page (index 3)
    ui->stackedWidget_2->setCurrentIndex(3);
}

void fishingport::on_btnLaunchPrediction_clicked() {
    QString nom = ui->lblPredBoatName->text().mid(6);  // after "Nom : "
    QString type = ui->lblPredBoatType->text().mid(7); // after "Type : "
    QString dateFab =
        ui->lblPredBoatDate->text().mid(23); // after "Date de fabrication : "
    QString age = ui->lblPredBoatAge->text().mid(6); // after "Âge : "
    QString sorties =
        ui->lblPredBoatSorties->text().mid(21); // after "Nombre de sorties : "

    ui->txtPredictionResult->clear();
    ui->lblPredJsonStatus->setText("État Général: En cours d'analyse...");
    ui->lblPredJsonYears->setText("-- ans");
    ui->progressBarHealth->setValue(0);
    ui->txtPredictionResult->append("<b>🔮 Analyse en cours (cela peut prendre "
                                    "quelques instants)...</b><br>");

    // Build prompt for Mistral requesting JSON format
    QString prompt =
        "Tu es un expert maritime spécialisé dans l'évaluation de la durée de "
        "vie des bateaux de pêche. "
        "Voici les informations d'un bateau :\n"
        "- Nom : " +
        nom +
        "\n"
        "- Type : " +
        type +
        "\n"
        "- Date de fabrication : " +
        dateFab +
        "\n"
        "- Âge actuel : " +
        age +
        "\n"
        "- Nombre total de sorties en mer : " +
        sorties +
        "\n\n"
        "Évalue ce bateau selon ces règles :\n"
        "1. Durée de vie typique : Petit ~15-20 ans, Moyen ~20-30 ans, Grand "
        "~25-40 ans.\n"
        "2. Usure : Plus de sorties accélère la dégradation.\n\n"
        "Réponds OBLIGATOIREMENT ET UNIQUEMENT avec un objet JSON strict au "
        "format suivant :\n"
        "{\n"
        "  \"score_sante\": (entier entre 0 et 100 représentant la santé "
        "restante),\n"
        "  \"statut\": (chaîne, l'une de ces trois : \"En bon état\", \"À "
        "surveiller\", ou \"Fin de vie\"),\n"
        "  \"annees_restantes\": (entier, le nombre approximatif d'années de vie "
        "restantes, 0 si fin de vie),\n"
        "  \"raisonnement\": (chaîne de caractères en français expliquant le "
        "diagnostic et des recommandations de maintenance)\n"
        "}\n"
        "Ne renvoie que le JSON, aucun texte avant ou après.";

    // Build JSON for Ollama
    QJsonObject json;
    json["model"] = "mistral";
    json["prompt"] = prompt;
    json["stream"] = false;  // Disable streaming to get clean JSON
    json["format"] = "json"; // Force Mistral to output JSON

    QJsonDocument doc(json);
    QByteArray data = doc.toJson();

    QUrl url("http://localhost:11434/api/generate");
    QNetworkRequest request(url);
    request.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");

    // Keep a buffer for response (if stream=false, it usually comes in one chunk
    // anyway)
    QString *responseBuffer = new QString();

    QNetworkReply *reply = networkManager->post(request, data);

    connect(reply, &QNetworkReply::readyRead, this, [reply, responseBuffer]() {
        *responseBuffer += QString::fromUtf8(reply->readAll());
    });

    connect(
        reply, &QNetworkReply::finished, this, [this, reply, responseBuffer]() {
            if (reply->error() != QNetworkReply::NoError) {
                ui->txtPredictionResult->setText(
                    "<b style='color:red;'>Erreur:</b> Impossible de joindre Ollama. "
                    "Assurez-vous qu'il est en cours d'exécution.");
                ui->lblPredJsonStatus->setText("Erreur réseau");
            } else {
                // Parse the Ollama specific JSON wrapper -> {"response": "{...}"}
                QJsonDocument responseDoc =
                    QJsonDocument::fromJson(responseBuffer->toUtf8());
                QJsonObject responseObj = responseDoc.object();
                QString innerJsonStr = responseObj["response"].toString();

                // Parse the inner Mistral JSON
                QJsonDocument innerDoc =
                    QJsonDocument::fromJson(innerJsonStr.toUtf8());
                if (innerDoc.isObject()) {
                    QJsonObject aiData = innerDoc.object();

                    int healthScore = aiData["score_sante"].toInt();
                    QString status = aiData["statut"].toString();
                    int yearsLeft = aiData["annees_restantes"].toInt();
                    QString reasoning = aiData["raisonnement"].toString();

                    // Update UI Elements
                    ui->progressBarHealth->setValue(healthScore);

                    if (healthScore > 66) {
                        ui->progressBarHealth->setStyleSheet(
                            "QProgressBar { border: 1px solid rgba(0,180,216,0.3); "
                            "border-radius: 6px; text-align: center; background-color: "
                            "#0A1628; color: #FFFFFF; font-weight: bold; } "
                            "QProgressBar::chunk { background-color: #2DC653; "
                            "border-radius: "
                            "5px; }");
                        ui->lblPredJsonStatus->setStyleSheet(
                            "color: #2DC653; font-size: 16px; font-weight: bold;");
                    } else if (healthScore > 33) {
                        ui->progressBarHealth->setStyleSheet(
                            "QProgressBar { border: 1px solid rgba(0,180,216,0.3); "
                            "border-radius: 6px; text-align: center; background-color: "
                            "#0A1628; color: #FFFFFF; font-weight: bold; } "
                            "QProgressBar::chunk { background-color: #F4A825; "
                            "border-radius: "
                            "5px; }");
                        ui->lblPredJsonStatus->setStyleSheet(
                            "color: #F4A825; font-size: 16px; font-weight: bold;");
                    } else {
                        ui->progressBarHealth->setStyleSheet(
                            "QProgressBar { border: 1px solid rgba(0,180,216,0.3); "
                            "border-radius: 6px; text-align: center; background-color: "
                            "#0A1628; color: #FFFFFF; font-weight: bold; } "
                            "QProgressBar::chunk { background-color: #E63946; "
                            "border-radius: "
                            "5px; }");
                        ui->lblPredJsonStatus->setStyleSheet(
                            "color: #E63946; font-size: 16px; font-weight: bold;");
                    }

                    ui->lblPredJsonStatus->setText("État Général: " + status);
                    ui->lblPredJsonYears->setText(QString::number(yearsLeft) + " ans");
                    ui->txtPredictionResult->setText(reasoning);
                } else {
                    ui->txtPredictionResult->setText("Erreur: L'IA n'a pas renvoyé un "
                                                     "format JSON valide.\n\nRéponse "
                                                     "brute:\n" +
                                                     innerJsonStr);
                    ui->lblPredJsonStatus->setText("Erreur d'analyse");
                }
            }

            delete responseBuffer;
            reply->deleteLater();
        });
}

void fishingport::on_btnPredictionRetour_clicked() {
  on_btnNavBateaux_clicked();
}

// ════════════════════════════════════════
//  NAVIGATION — EMPLACEMENTS
// ════════════════════════════════════════
void fishingport::on_btnNavEmplacements_clicked()
{
    ui->btnNavBateaux->setChecked(false);
    ui->btnNavEmplacements->setChecked(true);
    ui->stackedWidget->setCurrentIndex(1); // pageEmplacements

    Etmp.chargerQuais(ui->comboBox_7);
    Etmp.chargerBateaux(ui->comboBox_8);
    Etmp.chargerBateaux(ui->comboBox_16);
    afficherEmplacements();

    if (!m_histConnexionsBranchees) {
        m_histConnexionsBranchees = true;
        connect(ui->pushButton_19, &QPushButton::clicked,
                this, &fishingport::consulterHistorique);
        connect(ui->lineEdit_5, &QLineEdit::textChanged, this, &fishingport::rechercherHistorique);
        connect(ui->comboBox_14, QOverload<int>::of(&QComboBox::currentIndexChanged),
                this, &fishingport::consulterHistorique);
        connect(ui->comboBox_15, QOverload<int>::of(&QComboBox::currentIndexChanged),
                this, &fishingport::consulterHistorique);
        connect(ui->comboBox_16, QOverload<int>::of(&QComboBox::currentIndexChanged),
                this, &fishingport::lancerRecommandation);
        connect(ui->pushButton_20, &QPushButton::clicked,
                this, &fishingport::afficherAnalyseSaisonniere);
    }

    Etmp.afficherRecommandation(ui->widget_1, 0, nullptr);
}

// ════════════════════════════════════════
//  ICONES EMPLACEMENTS
// ════════════════════════════════════════
QIcon fishingport::creerIcone(const QString &type)
{
    QPixmap pixmap(32, 32);
    pixmap.fill(Qt::transparent);
    QPainter painter(&pixmap);
    painter.setRenderHint(QPainter::Antialiasing);

    if (type == "voir") {
        painter.setPen(QPen(Qt::white, 2.5));
        painter.setBrush(Qt::NoBrush);
        painter.drawEllipse(4, 10, 24, 12);
        painter.setBrush(Qt::white);
        painter.setPen(Qt::NoPen);
        painter.drawEllipse(10, 11, 12, 10);
        painter.setBrush(QColor(30, 80, 30));
        painter.drawEllipse(13, 13, 6, 6);
    } else if (type == "modifier") {
        painter.setPen(QPen(Qt::white, 2.5, Qt::SolidLine, Qt::RoundCap, Qt::RoundJoin));
        painter.setBrush(Qt::white);
        QPolygonF crayon;
        crayon << QPointF(6,22) << QPointF(10,26) << QPointF(26,10) << QPointF(22,6);
        painter.drawPolygon(crayon);
    } else if (type == "supprimer") {
        painter.setPen(QPen(Qt::white, 2.0, Qt::SolidLine, Qt::RoundCap, Qt::RoundJoin));
        painter.drawArc(11, 3, 10, 6, 0, 180*16);
        painter.drawRoundedRect(5, 7, 22, 3, 2, 2);
    }
    painter.end();
    return QIcon(pixmap);
}

// ════════════════════════════════════════
//  AJOUTER EMPLACEMENT
// ════════════════════════════════════════
void fishingport::on_pushButton_14_clicked()
{
    QString etat = "";
    if      (ui->radioButton_8->isChecked())  etat = "Libre";
    else if (ui->radioButton_9->isChecked())  etat = "Occupé";
    else if (ui->radioButton_10->isChecked()) etat = "Maintenance";

    if (ui->lineEdit_2->text().trimmed().isEmpty()) {
        QMessageBox::warning(this, "Champ manquant", "Veuillez saisir le Numéro !"); return;
    }
    if (etat.isEmpty()) {
        QMessageBox::warning(this, "Champ manquant", "Veuillez sélectionner un État !"); return;
    }

    Etmp.setNumero(ui->lineEdit_2->text().trimmed());
    Etmp.setType(ui->comboBox_13->currentText());
    Etmp.setIdQuais(ui->comboBox_7->currentData().toInt());
    Etmp.setDateDebut(ui->dateEdit_2->date());
    Etmp.setCommentaire(ui->textEdit->toPlainText().trimmed());
    Etmp.setIdBateau(ui->comboBox_8->currentData().toInt());
    Etmp.setEtat(etat);

    if (Etmp.ajouter()) {
        afficherEmplacements();
        ui->lineEdit_2->clear();
        ui->comboBox_13->setCurrentIndex(0);
        ui->comboBox_7->setCurrentIndex(0);
        ui->comboBox_8->setCurrentIndex(0);
        ui->textEdit->clear();
        ui->radioButton_8->setChecked(true);
    }
}

// ════════════════════════════════════════
//  CONFIRMER MODIFICATION EMPLACEMENT
// ════════════════════════════════════════
void fishingport::on_pushButton_15_clicked()
{
    int id = ui->lineEdit_2->property("idEmplacement").toInt();
    if (id == 0) {
        QMessageBox::warning(this, "Attention",
                             "Sélectionnez d'abord un emplacement à modifier !");
        return;
    }

    QString etat = "";
    if      (ui->radioButton_8->isChecked())  etat = "Libre";
    else if (ui->radioButton_9->isChecked())  etat = "Occupé";
    else if (ui->radioButton_10->isChecked()) etat = "Maintenance";

    Etmp.setId(id);
    Etmp.setNumero(ui->lineEdit_2->text().trimmed());
    Etmp.setType(ui->comboBox_13->currentText());
    Etmp.setIdQuais(ui->comboBox_7->currentData().toInt());
    Etmp.setDateDebut(ui->dateEdit_2->date());
    Etmp.setCommentaire(ui->textEdit->toPlainText().trimmed());
    Etmp.setEtat(etat);

    if (Etmp.modifier()) {
        afficherEmplacements();
        ui->lineEdit_2->setProperty("idEmplacement", 0);
        ui->lineEdit_2->clear();
        ui->radioButton_8->setChecked(true);
    }
}

// ════════════════════════════════════════
//  ANNULER EMPLACEMENT
// ════════════════════════════════════════
void fishingport::on_pushButton_16_clicked()
{
    ui->lineEdit_2->clear();
    ui->comboBox_13->setCurrentIndex(0);
    ui->comboBox_7->setCurrentIndex(0);
    ui->comboBox_8->setCurrentIndex(0);
    ui->dateEdit_2->setDate(QDate::currentDate());
    ui->textEdit->clear();
    ui->radioButton_8->setChecked(true);
    ui->lineEdit_2->setProperty("idEmplacement", 0);
    QMessageBox::information(this, "Annulé", "Formulaire réinitialisé !");
}

// ════════════════════════════════════════
//  COMMENTAIRE IA EMPLACEMENT
// ════════════════════════════════════════
void fishingport::on_pushButton_17_clicked()
{
    QString type = ui->comboBox_13->currentText();
    QString etat = "";
    if      (ui->radioButton_8->isChecked())  etat = "Libre";
    else if (ui->radioButton_9->isChecked())  etat = "Occupé";
    else if (ui->radioButton_10->isChecked()) etat = "Maintenance";

    if (type == "Aucun type" || type.isEmpty()) {
        QMessageBox::warning(this, "Attention", "Veuillez sélectionner un Type !"); return;
    }
    if (etat.isEmpty()) {
        QMessageBox::warning(this, "Attention", "Veuillez sélectionner un État !"); return;
    }
    ui->textEdit->setPlainText(Etmp.genererCommentaire(type, etat, ui->dateEdit_2->date()));
}

// ════════════════════════════════════════
//  AFFICHER EMPLACEMENTS
// ════════════════════════════════════════
void fishingport::afficherEmplacements()
{
    if (!Etmp.afficher(ui->tableWidget_3)) return;

    for (int ligne = 0; ligne < ui->tableWidget_3->rowCount(); ligne++) {
        ajouterBoutonsAction(ligne);
        ui->tableWidget_3->setRowHeight(ligne, 50);
    }

    if (!m_connexionsBranchees) {
        m_connexionsBranchees = true;

        connect(ui->lineEdit_2, &QLineEdit::textChanged, this, [this](const QString &texte) {
            bool ok;
            texte.trimmed().toInt(&ok);
            if (texte.isEmpty()) {
                ui->label_3->setText(""); ui->label_3->setStyleSheet("");
            } else if (!ok || texte.trimmed().toInt() <= 0) {
                ui->label_3->setText("Le numéro doit être un entier positif !");
                ui->label_3->setStyleSheet("color: #F44336; font-size: 11px; font-weight: bold;");
            } else {
                ui->label_3->setText("Numéro valide");
                ui->label_3->setStyleSheet("color: #4CAF50; font-size: 11px; font-weight: bold;");
            }
        });

        connect(ui->dateEdit_2, &QDateEdit::dateChanged, this, [this](const QDate &date) {
            QDate auj = QDate::currentDate();
            int ans = date.daysTo(auj) / 365;
            if (date > auj) {
                ui->label_4->setText("La date ne peut pas être dans le futur !");
                ui->label_4->setStyleSheet("color: #F44336; font-size: 11px; font-weight: bold;");
            } else if (ans > 50) {
                ui->label_4->setText("Date trop ancienne (+" + QString::number(ans) + " ans) !");
                ui->label_4->setStyleSheet("color: #F44336; font-size: 11px; font-weight: bold;");
            } else {
                ui->label_4->setText("Date valide (" + QString::number(ans) + " an(s))");
                ui->label_4->setStyleSheet("color: #4CAF50; font-size: 11px; font-weight: bold;");
            }
        });

        connect(ui->lineEdit_3, &QLineEdit::textChanged,
                this, &fishingport::rechercherEmplacements);
        connect(ui->comboBox_9, QOverload<int>::of(&QComboBox::currentIndexChanged),
                this, &fishingport::rechercherEmplacements);
        connect(ui->comboBox_10, QOverload<int>::of(&QComboBox::currentIndexChanged),
                this, &fishingport::trierEmplacements);
        connect(ui->comboBox_12, QOverload<int>::of(&QComboBox::currentIndexChanged),
                this, [this](int index) {
                    if      (index == 1) exporterPDF();
                    else if (index == 2) exporterExcel();
                    ui->comboBox_12->setCurrentIndex(0);
                });
    }
    mettreAJourStatistiquesEmp();
}

// ════════════════════════════════════════
//  BOUTONS ACTION EMPLACEMENT
// ════════════════════════════════════════
void fishingport::ajouterBoutonsAction(int ligne)
{
    QWidget     *w = new QWidget();
    QHBoxLayout *l = new QHBoxLayout(w);
    l->setContentsMargins(4,2,4,2);
    l->setSpacing(5);

    auto makeBtn = [](const QString &emoji, const QString &color,
                      const QString &hover, const QString &pressed,
                      const QString &tip) -> QPushButton* {
        QPushButton *btn = new QPushButton(emoji);
        btn->setFixedSize(36, 36);
        btn->setToolTip(tip);
        btn->setFont(QFont("Segoe UI Emoji", 14));
        btn->setStyleSheet(QString(
            "QPushButton { background-color: %1; border-radius: 8px; "
            "border: 1px solid %2; color: white; font-size: 16px; padding: 0px; }"
            "QPushButton:hover   { background-color: %2; }"
            "QPushButton:pressed { background-color: %3; }"
        ).arg(color, hover, pressed));
        return btn;
    };

    QPushButton *btnVoir      = makeBtn("\U0001F441", "#4CAF50", "#388E3C", "#2E7D32", "Voir");
    QPushButton *btnModifier  = makeBtn("\u270F",     "#FF9800", "#F57C00", "#E65100", "Modifier");
    QPushButton *btnSupprimer = makeBtn("\U0001F5D1", "#F44336", "#C62828", "#B71C1C", "Supprimer");

    connect(btnVoir,      &QPushButton::clicked, this, [this, ligne]() { voirEmplacement(ligne);      });
    connect(btnModifier,  &QPushButton::clicked, this, [this, ligne]() { modifierEmplacement(ligne);  });
    connect(btnSupprimer, &QPushButton::clicked, this, [this, ligne]() { supprimerEmplacement(ligne); });

    l->addWidget(btnVoir);
    l->addWidget(btnModifier);
    l->addWidget(btnSupprimer);
    l->addStretch();

    ui->tableWidget_3->setCellWidget(ligne, 7, w);
}

// ════════════════════════════════════════
//  VOIR EMPLACEMENT
// ════════════════════════════════════════
void fishingport::voirEmplacement(int row)
{
    auto cell = [&](int col) -> QString {
        return ui->tableWidget_3->item(row, col)
            ? ui->tableWidget_3->item(row, col)->text() : "";
    };

    QString message =
        "━━━━━━━━━━━━━━━━━━━━━━\n"
        "DÉTAILS EMPLACEMENT\n"
        "━━━━━━━━━━━━━━━━━━━━━━\n"
        "ID         : " + cell(0) + "\n"
        "Type       : " + cell(1) + "\n"
        "Numéro     : " + cell(2) + "\n"
        "Quai       : " + cell(3) + "\n"
        "État       : " + cell(4) + "\n"
        "Bateau     : " + cell(5) + "\n"
        "Date Début : " + cell(6) + "\n"
        "━━━━━━━━━━━━━━━━━━━━━━";

    QMessageBox::information(this, "Détails Emplacement", message);
}

// ════════════════════════════════════════
//  MODIFIER EMPLACEMENT
// ════════════════════════════════════════
void fishingport::modifierEmplacement(int row)
{
    auto cell = [&](int col) -> QString {
        return ui->tableWidget_3->item(row, col)
            ? ui->tableWidget_3->item(row, col)->text() : "";
    };

    ui->lineEdit_2->setText(cell(2));

    int indexType = ui->comboBox_13->findText(cell(1));
    if (indexType >= 0) ui->comboBox_13->setCurrentIndex(indexType);

    int indexQuai = ui->comboBox_7->findText(cell(3));
    if (indexQuai >= 0) ui->comboBox_7->setCurrentIndex(indexQuai);

    QString etat = cell(4);
    if      (etat == "Libre")       ui->radioButton_8->setChecked(true);
    else if (etat == "Occupé")      ui->radioButton_9->setChecked(true);
    else if (etat == "Maintenance") ui->radioButton_10->setChecked(true);

    QDate qdate = QDate::fromString(cell(6), "dd/MM/yyyy");
    if (qdate.isValid()) ui->dateEdit_2->setDate(qdate);

    ui->lineEdit_2->setProperty("idEmplacement", cell(0).toInt());
    QMessageBox::information(this, "Modifier",
                             "Modifiez les champs puis cliquez sur Confirmer !");
}

// ════════════════════════════════════════
//  SUPPRIMER EMPLACEMENT
// ════════════════════════════════════════
void fishingport::supprimerEmplacement(int row)
{
    QString id     = ui->tableWidget_3->item(row, 0)->text();
    QString numero = ui->tableWidget_3->item(row, 2)->text();

    QMessageBox::StandardButton reply = QMessageBox::question(
        this, "Confirmation",
        "Supprimer l'emplacement Numéro : " + numero + " ?",
        QMessageBox::Yes | QMessageBox::No);

    if (reply == QMessageBox::Yes) {
        Etmp.setId(id.toInt());
        if (Etmp.supprimer()) afficherEmplacements();
    }
}

// ════════════════════════════════════════
//  RECHERCHER EMPLACEMENTS
// ════════════════════════════════════════
void fishingport::rechercherEmplacements()
{
    Etmp.rechercher(ui->tableWidget_3,
                    ui->lineEdit_3->text(),
                    ui->comboBox_9->currentText());
    mettreAJourStatistiquesEmp();
}

// ════════════════════════════════════════
//  TRIER EMPLACEMENTS
// ════════════════════════════════════════
void fishingport::trierEmplacements()
{
    Etmp.trier(ui->tableWidget_3, ui->comboBox_10->currentText());
}

// ════════════════════════════════════════
//  EXPORTER PDF
// ════════════════════════════════════════
void fishingport::exporterPDF()
{
    Etmp.exporterPDF(ui->tableWidget_3, this);
}

// ════════════════════════════════════════
//  EXPORTER EXCEL
// ════════════════════════════════════════
void fishingport::exporterExcel()
{
    Etmp.exporterExcel(ui->tableWidget_3, this);
}

// ════════════════════════════════════════
//  STATISTIQUES EMPLACEMENTS
// ════════════════════════════════════════
void fishingport::mettreAJourStatistiquesEmp()
{
    Etmp.mettreAJourStatistiques(
        ui->tableWidget_3,
        ui->label_25,
        ui->label_26,
        ui->label_27,
        ui->label_28);
}

// ════════════════════════════════════════
//  CONSULTER HISTORIQUE
// ════════════════════════════════════════
void fishingport::consulterHistorique()
{
    QString filtre    = ui->comboBox_14->currentText();
    QString ordre     = ui->comboBox_15->currentText();
    QString recherche = ui->lineEdit_5->text().trimmed();
    Etmp.afficherHistorique(ui->widget_historique, filtre, ordre, recherche);
}

// ════════════════════════════════════════
//  RECHERCHER HISTORIQUE
// ════════════════════════════════════════
void fishingport::rechercherHistorique()
{
    consulterHistorique();
}

// ════════════════════════════════════════
//  FRISE DIALOG
// ════════════════════════════════════════
void fishingport::afficherFriseDialog(int idEmplacement)
{
    QSqlQuery q;
    q.prepare(
        "SELECT e.numero, e.etate, e.date_debut_occ, q.nomq, b.nomb "
        "FROM emplacement e "
        "LEFT JOIN quai q ON e.id_quais = q.id_quais "
        "LEFT JOIN bateau b ON b.id_emplacement = e.id_emplacement "
        "WHERE e.id_emplacement = :id");
    q.bindValue(":id", idEmplacement);

    if (!q.exec() || !q.next()) {
        QMessageBox::warning(this, "Erreur", "Emplacement introuvable !"); return;
    }

    QString numero = q.value(0).toString();
    QString etat   = q.value(1).toString();
    QDate   debut  = q.value(2).toDate();
    QString quai   = q.value(3).toString();
    QString bateau = q.value(4).toString();
    if (bateau.isEmpty()) bateau = "Aucun";

    int jours = debut.daysTo(QDate::currentDate());
    int mois  = jours / 30;

    QDialog *dlg = new QDialog(this);
    dlg->setWindowTitle("Frise — Emplacement " + numero);
    dlg->resize(520, 300);

    QVBoxLayout *layout = new QVBoxLayout(dlg);
    layout->setContentsMargins(20, 20, 20, 20);
    layout->setSpacing(12);

    QLabel *titre = new QLabel(
        QString("Emp. %1   —   Quai : %2   —   Bateau : %3")
            .arg(numero, quai.isEmpty() ? "—" : quai, bateau));
    titre->setStyleSheet(
        "font-weight: bold; font-size: 14px; color: #0D47A1;"
        "padding: 12px 16px; background: #E3F2FD; border-radius: 10px;");
    titre->setAlignment(Qt::AlignCenter);
    layout->addWidget(titre);

    QLabel *info = new QLabel(
        QString("État actuel : %1   —   depuis %2 jour(s)  (%3 mois)")
            .arg(etat.isEmpty() ? "Inconnu" : etat).arg(jours).arg(mois));
    info->setStyleSheet("font-size: 12px; color: #546E7A; padding: 4px 8px;");
    info->setAlignment(Qt::AlignCenter);
    layout->addWidget(info);

    QString couleur = "#90CAF9";
    if      (etat == "Libre")       couleur = "#97C459";
    else if (etat == "Occupé")      couleur = "#E24B4A";
    else if (etat == "Maintenance") couleur = "#EF9F27";

    QProgressBar *barre = new QProgressBar();
    barre->setRange(0, 100); barre->setValue(100);
    barre->setTextVisible(false); barre->setFixedHeight(30);
    barre->setStyleSheet(QString(
        "QProgressBar { border-radius: 10px; background: #ECEFF1; border: none; }"
        "QProgressBar::chunk { border-radius: 10px; background: %1; }").arg(couleur));
    layout->addWidget(barre);

    layout->addStretch();

    QPushButton *btnFermer = new QPushButton("Fermer");
    btnFermer->setStyleSheet(
        "QPushButton { background: #0288D1; color: white; border-radius: 8px;"
        "padding: 9px 28px; font-weight: bold; font-size: 13px; border: none; }"
        "QPushButton:hover { background: #0277BD; }");
    connect(btnFermer, &QPushButton::clicked, dlg, &QDialog::close);

    QHBoxLayout *btnRow = new QHBoxLayout();
    btnRow->addStretch(); btnRow->addWidget(btnFermer);
    layout->addLayout(btnRow);

    dlg->exec();
}

// ════════════════════════════════════════
//  STATISTIQUES GRAPHIQUES EMPLACEMENTS
// ════════════════════════════════════════
void fishingport::on_comboBox_11_currentIndexChanged(int /*index*/)
{
    QString critere = ui->comboBox_11->currentText();
    if (critere.isEmpty() || critere == "voir par:") return;

    int total = 0, libres = 0, occupes = 0, maintenance = 0;
    QMap<QString, int> parType, parQuai;
    QMap<QString, int> libresParQuai, occupesParQuai, maintenanceParQuai;

    for (int i = 0; i < ui->tableWidget_3->rowCount(); i++) {
        if (ui->tableWidget_3->isRowHidden(i)) continue;
        total++;
        QString etat = ui->tableWidget_3->item(i,4) ? ui->tableWidget_3->item(i,4)->text() : "";
        QString type = ui->tableWidget_3->item(i,1) ? ui->tableWidget_3->item(i,1)->text() : "?";
        QString quai = ui->tableWidget_3->item(i,3) ? ui->tableWidget_3->item(i,3)->text() : "?";

        if      (etat == "Libre")       libres++;
        else if (etat == "Occupé")      occupes++;
        else if (etat == "Maintenance") maintenance++;

        if (!type.isEmpty()) parType[type]++;
        if (!quai.isEmpty()) {
            parQuai[quai]++;
            if      (etat == "Libre")       libresParQuai[quai]++;
            else if (etat == "Occupé")      occupesParQuai[quai]++;
            else if (etat == "Maintenance") maintenanceParQuai[quai]++;
        }
    }

    // ── Dialog ────────────────────────────────────────────────
    QDialog *dlg = new QDialog(this);
    dlg->setWindowTitle("Statistiques — " + critere);
    dlg->resize(820, 700);
    dlg->setStyleSheet(
        "QDialog { background: #F4F7FB; }"
        "QLabel  { border: none; background: transparent; }"
        "QFrame  { border: none; }");

    QVBoxLayout *root = new QVBoxLayout(dlg);
    root->setContentsMargins(20, 20, 20, 20);
    root->setSpacing(16);

    // ── Header gradient ───────────────────────────────────────
    QFrame *headerFrame = new QFrame();
    headerFrame->setFixedHeight(70);
    headerFrame->setStyleSheet(
        "QFrame { background: qlineargradient(x1:0,y1:0,x2:1,y2:0,"
        "stop:0 #1565C0, stop:0.5 #0288D1, stop:1 #00BCD4);"
        "border-radius: 14px; }");
    QHBoxLayout *headerL = new QHBoxLayout(headerFrame);
    headerL->setContentsMargins(20, 0, 20, 0);

    QLabel *headerIcon = new QLabel("📊");
    headerIcon->setStyleSheet("font-size: 26px;");
    QLabel *headerTitle = new QLabel("Statistiques — " + critere.toUpper());
    headerTitle->setStyleSheet(
        "font-size: 18px; font-weight: bold; color: white; letter-spacing: 1px;");
    QLabel *headerSub = new QLabel(QString::number(total) + " emplacements analysés");
    headerSub->setStyleSheet("font-size: 11px; color: rgba(255,255,255,0.8);");

    QVBoxLayout *headerText = new QVBoxLayout();
    headerText->setSpacing(2);
    headerText->addWidget(headerTitle);
    headerText->addWidget(headerSub);

    headerL->addWidget(headerIcon);
    headerL->addSpacing(10);
    headerL->addLayout(headerText);
    headerL->addStretch();
    root->addWidget(headerFrame);

    // ── KPI Cards ─────────────────────────────────────────────
    int tauxOcc = (total > 0) ? occupes     * 100 / total : 0;
    int tauxLib = (total > 0) ? libres      * 100 / total : 0;
    int tauxMnt = (total > 0) ? maintenance * 100 / total : 0;

    QHBoxLayout *kpiRow = new QHBoxLayout();
    kpiRow->setSpacing(12);

    struct KPI {
        QString nom, valeur, icon, couleur, bg, border;
    };
    QList<KPI> kpis = {
        {"Total",       QString::number(total),
         "⚓", "#1565C0", "#E3F2FD", "#90CAF9"},
        {"Libres",      QString::number(libres) + "\n" + QString::number(tauxLib) + "%",
         "🟢", "#2E7D32", "#E8F5E9", "#A5D6A7"},
        {"Occupés",     QString::number(occupes) + "\n" + QString::number(tauxOcc) + "%",
         "🔴", "#C62828", "#FFEBEE", "#EF9A9A"},
        {"Maintenance", QString::number(maintenance) + "\n" + QString::number(tauxMnt) + "%",
         "🔧", "#E65100", "#FFF3E0", "#FFCC80"}
    };

    for (const KPI &k : kpis) {
        QFrame *card = new QFrame();
        card->setFixedHeight(100);
        card->setStyleSheet(QString(
                                "QFrame { background: %1; border: 2px solid %2; "
                                "border-radius: 16px; }").arg(k.bg, k.border));

        QVBoxLayout *cl = new QVBoxLayout(card);
        cl->setContentsMargins(12, 8, 12, 8);
        cl->setSpacing(3);
        cl->setAlignment(Qt::AlignCenter);

        QLabel *iconLbl = new QLabel(k.icon);
        iconLbl->setAlignment(Qt::AlignCenter);
        iconLbl->setStyleSheet("font-size: 20px;");

        // Valeur principale (nombre)
        QString parts = k.valeur;
        QStringList sp = parts.split("\n");
        QLabel *valLbl = new QLabel(sp[0]);
        valLbl->setAlignment(Qt::AlignCenter);
        valLbl->setStyleSheet(QString(
                                  "font-size: 22px; font-weight: bold; color: %1;").arg(k.couleur));

        QLabel *nomLbl = new QLabel(k.nom + (sp.size()>1 ? "  " + sp[1] : ""));
        nomLbl->setAlignment(Qt::AlignCenter);
        nomLbl->setStyleSheet(QString(
                                  "font-size: 11px; font-weight: bold; color: %1;").arg(k.couleur));

        cl->addWidget(iconLbl);
        cl->addWidget(valLbl);
        cl->addWidget(nomLbl);
        kpiRow->addWidget(card);
    }
    root->addLayout(kpiRow);

    // ── Barre occupation segmentée ─────────────────────────────
    QFrame *tauxFrame = new QFrame();
    tauxFrame->setStyleSheet(
        "QFrame { background: white; border-radius: 12px; "
        "border: 1px solid #E0E0E0; }");
    QVBoxLayout *tauxL = new QVBoxLayout(tauxFrame);
    tauxL->setContentsMargins(16, 12, 16, 12);
    tauxL->setSpacing(8);

    QHBoxLayout *tauxHeader = new QHBoxLayout();
    QLabel *tauxTitle = new QLabel("Taux d'occupation global");
    tauxTitle->setStyleSheet(
        "font-size: 13px; font-weight: bold; color: #1565C0;");
    QLabel *tauxPct = new QLabel(QString::number(tauxOcc) + "%");
    tauxPct->setStyleSheet(
        "font-size: 20px; font-weight: bold; color: #C62828;");
    tauxHeader->addWidget(tauxTitle);
    tauxHeader->addStretch();
    tauxHeader->addWidget(tauxPct);
    tauxL->addLayout(tauxHeader);

    // Barre segmentée
    QWidget *barWidget = new QWidget();
    barWidget->setFixedHeight(24);
    barWidget->setStyleSheet(
        "background: #ECEFF1; border-radius: 12px;");
    QHBoxLayout *barL = new QHBoxLayout(barWidget);
    barL->setContentsMargins(0,0,0,0);
    barL->setSpacing(1);

    auto addSeg = [&](int pct, const QString &color,
                      const QString &label, bool isFirst, bool isLast) {
        if (pct <= 0) return;
        QFrame *seg = new QFrame();
        seg->setFixedHeight(24);
        int w = qMax(8, pct * 7);
        seg->setFixedWidth(w);
        QString radius = "border-radius: 0px;";
        if (isFirst && isLast) radius = "border-radius: 12px;";
        else if (isFirst)      radius = "border-radius: 12px 0 0 12px;";
        else if (isLast)       radius = "border-radius: 0 12px 12px 0;";
        seg->setStyleSheet(QString(
                               "QFrame { background: %1; %2 border: none; }").arg(color, radius));
        seg->setToolTip(label + " : " + QString::number(pct) + "%");
        barL->addWidget(seg);
    };

    bool hasLib = tauxLib > 0, hasOcc = tauxOcc > 0, hasMnt = tauxMnt > 0;
    addSeg(tauxLib, "#4CAF50", "Libres",      hasLib,  !hasOcc && !hasMnt);
    addSeg(tauxOcc, "#F44336", "Occupés",     !hasLib, !hasMnt);
    addSeg(tauxMnt, "#FF9800", "Maintenance", !hasLib && !hasOcc, true);
    barL->addStretch();
    tauxL->addWidget(barWidget);

    // Légende
    QHBoxLayout *legL = new QHBoxLayout();
    struct Leg { QString col, txt; int pct; };
    for (const Leg &lg : QList<Leg>{
                                    {"#4CAF50","Libres",tauxLib},
                                    {"#F44336","Occupés",tauxOcc},
                                    {"#FF9800","Maintenance",tauxMnt}}) {
        QFrame *dot = new QFrame();
        dot->setFixedSize(10,10);
        dot->setStyleSheet(QString(
                               "QFrame { background: %1; border-radius: 5px; border: none; }").arg(lg.col));
        QLabel *lbl = new QLabel(lg.txt + "  " + QString::number(lg.pct) + "%");
        lbl->setStyleSheet("font-size: 11px; color: #546E7A;");
        legL->addWidget(dot);
        legL->addWidget(lbl);
        legL->addSpacing(16);
    }
    legL->addStretch();
    tauxL->addLayout(legL);
    root->addWidget(tauxFrame);

    // ══════════════════════════════════════════════════════════
    //  GRAPHIQUES SELON CRITÈRE
    // ══════════════════════════════════════════════════════════

    if (critere == "état") {
        // ── Donut amélioré ────────────────────────────────────
        QPieSeries *pie = new QPieSeries();
        pie->setHoleSize(0.45);
        pie->setPieSize(0.75);

        struct Slice { QString lbl; int val; QColor col; };
        for (const Slice &s : QList<Slice>{
                                           {"Libres",      libres,      QColor(76,175,80)},
                                           {"Occupés",     occupes,     QColor(244,67,54)},
                                           {"Maintenance", maintenance, QColor(255,152,0)}}) {
            if (s.val <= 0) continue;
            QPieSlice *sl = pie->append(
                QString("%1\n%2  (%3%)")
                    .arg(s.lbl).arg(s.val)
                    .arg(total > 0 ? s.val*100/total : 0), s.val);
            sl->setColor(s.col);
            sl->setBorderColor(Qt::white);
            sl->setBorderWidth(4);
            sl->setLabelVisible(true);
            sl->setLabelFont(QFont("Segoe UI", 11, QFont::Bold));
            sl->setExploded(true);
            sl->setExplodeDistanceFactor(0.04f);
        }

        QChart *chart = new QChart();
        chart->addSeries(pie);
        chart->setTitle("Répartition des états");
        chart->setTitleFont(QFont("Segoe UI", 14, QFont::Bold));
        chart->setBackgroundBrush(Qt::transparent);
        chart->setAnimationOptions(QChart::AllAnimations);
        chart->setAnimationDuration(800);
        chart->legend()->setAlignment(Qt::AlignBottom);
        chart->legend()->setFont(QFont("Segoe UI", 10));

        QChartView *cv = new QChartView(chart);
        cv->setRenderHint(QPainter::Antialiasing);
        cv->setMinimumHeight(300);
        cv->setStyleSheet(
            "QChartView { background: white; border-radius: 14px; "
            "border: 1px solid #E0E0E0; }");
        root->addWidget(cv);
    }

    else if (critere == "type") {
        // ── Une barre par type — couleur unique par type ──────
        // Palette de couleurs distinctes
        QList<QColor> palette = {
            QColor(33,150,243),   // bleu    Commerce
            QColor(76,175,80),    // vert    Plaisance
            QColor(255,152,0),    // orange  Plaisance légère
            QColor(156,39,176),   // violet  Plaisance lourde
            QColor(244,67,54),    // rouge   Pêche
            QColor(0,188,212),    // cyan    Pêche artisanale
            QColor(255,87,34),    // deep orange  Mixte
            QColor(233,30,99),    // rose    Spécial
            QColor(96,125,139),   // gris    Maintenance
            QColor(255,235,59),   // jaune   Urgence
        };

        // ✅ CORRECTION : une série séparée par type
        // (pour que chaque type apparaisse sur sa propre catégorie)
        QStringList categories;
        QList<int> valeurs;
        QList<QColor> couleurs;

        int ci = 0;
        for (auto it = parType.begin(); it != parType.end(); ++it, ++ci) {
            categories << it.key();
            valeurs    << it.value();
            couleurs   << palette[ci % palette.size()];
        }

        // Créer une QBarSeries avec UN QBarSet par type
        // mais une seule catégorie par set → chaque barre est indépendante
        QBarSeries *barSeries = new QBarSeries();
        for (int i = 0; i < categories.size(); i++) {
            QBarSet *set = new QBarSet(categories[i]);
            set->setColor(couleurs[i]);
            set->setLabelColor(Qt::white);
            // Ajouter 0 pour toutes les catégories sauf la sienne
            for (int j = 0; j < categories.size(); j++)
                *set << (j == i ? valeurs[i] : 0);
            barSeries->append(set);
        }
        barSeries->setLabelsVisible(true);
        barSeries->setLabelsPosition(QAbstractBarSeries::LabelsOutsideEnd);

        QBarCategoryAxis *axX = new QBarCategoryAxis();
        axX->append(categories);
        axX->setLabelsFont(QFont("Segoe UI", 9));

        QValueAxis *axY = new QValueAxis();
        axY->setLabelFormat("%d");
        axY->setTickCount(6);
        axY->setTitleText("Nombre d'emplacements");
        axY->setTitleFont(QFont("Segoe UI", 9));

        QChart *chart = new QChart();
        chart->addSeries(barSeries);
        chart->setTitle("Emplacements par type");
        chart->setTitleFont(QFont("Segoe UI", 14, QFont::Bold));
        chart->setBackgroundBrush(Qt::transparent);
        chart->setAnimationOptions(QChart::SeriesAnimations);
        chart->setAnimationDuration(600);
        chart->addAxis(axX, Qt::AlignBottom);
        chart->addAxis(axY, Qt::AlignLeft);
        barSeries->attachAxis(axX);
        barSeries->attachAxis(axY);
        chart->legend()->setAlignment(Qt::AlignBottom);
        chart->legend()->setFont(QFont("Segoe UI", 9));

        QChartView *cv = new QChartView(chart);
        cv->setRenderHint(QPainter::Antialiasing);
        cv->setMinimumHeight(300);
        cv->setStyleSheet(
            "QChartView { background: white; border-radius: 14px; "
            "border: 1px solid #E0E0E0; }");
        root->addWidget(cv);
    }

    else if (critere == "quai") {
        // ── Barres groupées par quai ──────────────────────────
        QBarSet *setLibres      = new QBarSet("Libres");
        QBarSet *setOccupes     = new QBarSet("Occupés");
        QBarSet *setMaintenance = new QBarSet("Maintenance");

        setLibres->setColor(QColor(76,175,80));
        setLibres->setBorderColor(QColor(56,142,60));
        setOccupes->setColor(QColor(244,67,54));
        setOccupes->setBorderColor(QColor(198,40,40));
        setMaintenance->setColor(QColor(255,152,0));
        setMaintenance->setBorderColor(QColor(230,81,0));

        QStringList quais;
        for (auto it = parQuai.begin(); it != parQuai.end(); ++it) {
            quais << it.key();
            *setLibres      << libresParQuai.value(it.key(), 0);
            *setOccupes     << occupesParQuai.value(it.key(), 0);
            *setMaintenance << maintenanceParQuai.value(it.key(), 0);
        }

        QBarSeries *barSeries = new QBarSeries();
        barSeries->append(setLibres);
        barSeries->append(setOccupes);
        barSeries->append(setMaintenance);
        barSeries->setLabelsVisible(true);
        barSeries->setLabelsPosition(QAbstractBarSeries::LabelsInsideEnd);

        QBarCategoryAxis *axX = new QBarCategoryAxis();
        axX->append(quais);
        axX->setLabelsFont(QFont("Segoe UI", 9));

        QValueAxis *axY = new QValueAxis();
        axY->setLabelFormat("%d");
        axY->setTickCount(6);
        axY->setTitleText("Nombre d'emplacements");
        axY->setTitleFont(QFont("Segoe UI", 9));

        QChart *chart = new QChart();
        chart->addSeries(barSeries);
        chart->setTitle("Répartition par quai");
        chart->setTitleFont(QFont("Segoe UI", 14, QFont::Bold));
        chart->setBackgroundBrush(Qt::transparent);
        chart->setAnimationOptions(QChart::SeriesAnimations);
        chart->setAnimationDuration(600);
        chart->legend()->setAlignment(Qt::AlignBottom);
        chart->legend()->setFont(QFont("Segoe UI", 10));
        chart->addAxis(axX, Qt::AlignBottom);
        chart->addAxis(axY, Qt::AlignLeft);
        barSeries->attachAxis(axX);
        barSeries->attachAxis(axY);

        QChartView *cv = new QChartView(chart);
        cv->setRenderHint(QPainter::Antialiasing);
        cv->setMinimumHeight(300);
        cv->setStyleSheet(
            "QChartView { background: white; border-radius: 14px; "
            "border: 1px solid #E0E0E0; }");
        root->addWidget(cv);
    }

    else if (critere == "num") {

        QScrollArea *scroll = new QScrollArea();
        scroll->setWidgetResizable(true);
        scroll->setFrameShape(QFrame::NoFrame);
        scroll->setStyleSheet("QScrollArea { background: transparent; border: none; }");
        scroll->setMinimumHeight(300);

        QWidget *inner = new QWidget();
        inner->setStyleSheet("background: transparent;");
        QVBoxLayout *ll = new QVBoxLayout(inner);
        ll->setContentsMargins(0,0,0,0); ll->setSpacing(7);
        scroll->setWidget(inner);

        for (int i = 0; i < ui->tableWidget_3->rowCount(); i++) {
            if (ui->tableWidget_3->isRowHidden(i)) continue;

            QString num  = ui->tableWidget_3->item(i,2) ? ui->tableWidget_3->item(i,2)->text() : "-";
            QString type = ui->tableWidget_3->item(i,1) ? ui->tableWidget_3->item(i,1)->text() : "-";
            QString quai = ui->tableWidget_3->item(i,3) ? ui->tableWidget_3->item(i,3)->text() : "-";
            QString etat = ui->tableWidget_3->item(i,4) ? ui->tableWidget_3->item(i,4)->text() : "-";
            QString date = ui->tableWidget_3->item(i,6) ? ui->tableWidget_3->item(i,6)->text() : "-";

            QString bColor = (etat=="Libre") ? "#4CAF50" : (etat=="Occupé") ? "#F44336" : "#FF9800";
            QString bgLine = (etat=="Libre") ? "#F1F8E9" : (etat=="Occupé") ? "#FFF3F3" : "#FFFDE7";

            QFrame *row = new QFrame();
            row->setStyleSheet(QString(
                                   "QFrame { background: %1; border-radius: 10px; border-left: 4px solid %2; }"
                                   ).arg(bgLine, bColor));

            QHBoxLayout *rl = new QHBoxLayout(row);
            rl->setContentsMargins(10,7,10,7); rl->setSpacing(12);

            QLabel *numLbl = new QLabel(QString("N° %1").arg(num));
            numLbl->setStyleSheet(QString(
                                      "font-weight: bold; color: %1; font-size: 13px;"
                                      ).arg(bColor));

            QLabel *infoLbl = new QLabel(
                QString("🏷 %1   ⚓ %2   📅 %3").arg(type, quai, date));
            infoLbl->setStyleSheet("color: #546E7A; font-size: 11px;");

            QLabel *etatBadge = new QLabel(etat);
            etatBadge->setFixedHeight(24);
            etatBadge->setAlignment(Qt::AlignCenter);
            etatBadge->setStyleSheet(QString(
                                         "background: %1; color: white; border-radius: 8px;"
                                         "padding: 0 10px; font-weight: bold; font-size: 11px;"
                                         ).arg(bColor));

            rl->addWidget(numLbl);
            rl->addWidget(infoLbl, 1);
            rl->addWidget(etatBadge);
            ll->addWidget(row);
        }
        ll->addStretch();
        root->addWidget(scroll);
    }

    // ── Bouton Fermer ──────────────────────────────────────────
    QHBoxLayout *btnRow = new QHBoxLayout();
    btnRow->addStretch();
    QPushButton *btnFermer = new QPushButton("  Fermer  ");
    btnFermer->setFixedHeight(40);
    btnFermer->setStyleSheet(
        "QPushButton { background: qlineargradient(x1:0,y1:0,x2:1,y2:0,"
        "stop:0 #1565C0, stop:1 #0288D1); color: white; border-radius: 10px;"
        "padding: 0 30px; font-weight: bold; font-size: 13px; border: none; }"
        "QPushButton:hover { background: #0D47A1; }");
    connect(btnFermer, &QPushButton::clicked, dlg, &QDialog::close);
    btnRow->addWidget(btnFermer);
    root->addLayout(btnRow);

    ui->comboBox_11->blockSignals(true);
    ui->comboBox_11->setCurrentIndex(0);
    ui->comboBox_11->blockSignals(false);

    dlg->exec();
}

// ════════════════════════════════════════
//  LANCER RECOMMANDATION
// ════════════════════════════════════════
void fishingport::lancerRecommandation()
{
    int idBateau = ui->comboBox_16->currentData().toInt();
    Etmp.afficherRecommandation(
        ui->widget_1,
        idBateau,
        [this](int idEmp, int idBat, const QString &num, const QString &quai,
               int score, const QString &nomB, const QString &typeB) {
            afficherModalAttribution(idEmp, idBat, num, quai, score, nomB, typeB);
        });
}

// ════════════════════════════════════════
//  MODAL ATTRIBUTION
// ════════════════════════════════════════
void fishingport::afficherModalAttribution(int idEmp, int idBateau,
                                           const QString &numero,
                                           const QString &quai,
                                           int score,
                                           const QString &nomBateau,
                                           const QString &typeBateau)
{
    QDialog *dlg = new QDialog(this);
    dlg->setWindowTitle("Confirmation d'attribution");
    dlg->setFixedWidth(420);
    dlg->setStyleSheet(
        "QDialog { background: #FAFAFA; }"
        "QLabel  { border: none; background: transparent; }"
        "QFrame  { border: none; }");

    QVBoxLayout *root = new QVBoxLayout(dlg);
    root->setContentsMargins(0, 0, 0, 0);
    root->setSpacing(0);

    QFrame *headerFrame = new QFrame();
    headerFrame->setStyleSheet("QFrame { background: #0288D1; }");
    QHBoxLayout *headerL = new QHBoxLayout(headerFrame);
    headerL->setContentsMargins(18, 14, 18, 14);

    QLabel *headerTitle = new QLabel("Confirmation d'attribution");
    headerTitle->setStyleSheet("font-size: 15px; font-weight: bold; color: white;");
    headerL->addWidget(headerTitle);
    headerL->addStretch();

    QPushButton *btnX = new QPushButton("✕");
    btnX->setFixedSize(28, 28);
    btnX->setStyleSheet(
        "QPushButton { background: transparent; color: white; border: none; "
        "font-size: 14px; font-weight: bold; }"
        "QPushButton:hover { background: rgba(255,255,255,0.2); border-radius: 14px; }");
    connect(btnX, &QPushButton::clicked, dlg, &QDialog::reject);
    headerL->addWidget(btnX);
    root->addWidget(headerFrame);

    QWidget *body = new QWidget();
    QVBoxLayout *bodyL = new QVBoxLayout(body);
    bodyL->setContentsMargins(20, 16, 20, 16);
    bodyL->setSpacing(12);

    auto makeSection = [](const QString &titre) -> QLabel* {
        QLabel *l = new QLabel(titre.toUpper());
        l->setStyleSheet(
            "font-size: 10px; font-weight: bold; color: #90A4AE; letter-spacing: 1px;");
        return l;
    };

    auto makeRow = [](const QString &cle, const QString &val,
                      bool bold = false) -> QHBoxLayout* {
        QHBoxLayout *row = new QHBoxLayout();
        QLabel *k = new QLabel(cle);
        k->setStyleSheet("font-size: 13px; color: #78909C;");
        QLabel *v = new QLabel(val);
        v->setStyleSheet(QString("font-size: 13px; color: #263238; %1")
                             .arg(bold ? "font-weight: bold;" : ""));
        row->addWidget(k); row->addStretch(); row->addWidget(v);
        return row;
    };

    bodyL->addWidget(makeSection("Fiche bateau"));
    QFrame *sep0 = new QFrame();
    sep0->setFixedHeight(1);
    sep0->setStyleSheet("background: #ECEFF1;");
    bodyL->addWidget(sep0);
    bodyL->addLayout(makeRow("Nom", nomBateau, true));
    bodyL->addLayout(makeRow("Type", typeBateau.isEmpty() ? "—" : typeBateau));
    bodyL->addLayout(makeRow("Quai habituel", quai.isEmpty() ? "—" : quai));

    QHBoxLayout *etatRow = new QHBoxLayout();
    QLabel *etatK = new QLabel("État");
    etatK->setStyleSheet("font-size: 13px; color: #78909C;");
    QLabel *etatV = new QLabel("Actif");
    etatV->setStyleSheet(
        "font-size: 11px; font-weight: bold; color: #2E7D32; "
        "background: #E8F5E9; border-radius: 10px; padding: 2px 12px;");
    etatRow->addWidget(etatK); etatRow->addStretch(); etatRow->addWidget(etatV);
    bodyL->addLayout(etatRow);

    QFrame *sep1 = new QFrame();
    sep1->setFixedHeight(1);
    sep1->setStyleSheet("background: #ECEFF1; margin: 4px 0;");
    bodyL->addWidget(sep1);

    bodyL->addWidget(makeSection("Emplacement attribué"));
    QFrame *sep2 = new QFrame();
    sep2->setFixedHeight(1);
    sep2->setStyleSheet("background: #ECEFF1;");
    bodyL->addWidget(sep2);

    QFrame *scoreBox = new QFrame();
    scoreBox->setStyleSheet("QFrame { background: #E8F5E9; border-radius: 8px; }");
    QHBoxLayout *scoreL = new QHBoxLayout(scoreBox);
    scoreL->setContentsMargins(12, 8, 12, 8);
    QLabel *scoreTxt = new QLabel("Score de compatibilité");
    scoreTxt->setStyleSheet("font-size: 12px; color: #2E7D32; font-weight: bold;");
    QLabel *scoreVal = new QLabel(QString::number(score) + "%");
    scoreVal->setStyleSheet("font-size: 18px; font-weight: bold; color: #2E7D32;");
    scoreL->addWidget(scoreTxt); scoreL->addStretch(); scoreL->addWidget(scoreVal);
    bodyL->addWidget(scoreBox);

    bodyL->addLayout(makeRow("Numéro", "Emp. " + numero, true));
    bodyL->addLayout(makeRow("Quai",   quai.isEmpty() ? "—" : quai));

    QHBoxLayout *nEtatRow = new QHBoxLayout();
    QLabel *nEtatK = new QLabel("Nouvel état");
    nEtatK->setStyleSheet("font-size: 13px; color: #78909C;");
    QLabel *nEtatV = new QLabel("Occupé");
    nEtatV->setStyleSheet(
        "font-size: 11px; font-weight: bold; color: #B71C1C; "
        "background: #FFEBEE; border-radius: 10px; padding: 2px 12px;");
    nEtatRow->addWidget(nEtatK); nEtatRow->addStretch(); nEtatRow->addWidget(nEtatV);
    bodyL->addLayout(nEtatRow);

    QFrame *sep3 = new QFrame();
    sep3->setFixedHeight(1);
    sep3->setStyleSheet("background: #ECEFF1; margin: 4px 0;");
    bodyL->addWidget(sep3);

    QLabel *note = new QLabel(
        "Après confirmation, l'état passe à <b>Occupé</b> "
        "et un log est enregistré dans l'historique.");
    note->setWordWrap(true);
    note->setTextFormat(Qt::RichText);
    note->setStyleSheet("font-size: 11px; color: #90A4AE;");
    bodyL->addWidget(note);
    root->addWidget(body);

    QFrame *footer = new QFrame();
    footer->setStyleSheet(
        "QFrame { background: #FAFAFA; border-top: 1px solid #ECEFF1; }");
    QHBoxLayout *footerL = new QHBoxLayout(footer);
    footerL->setContentsMargins(20, 12, 20, 12);
    footerL->setSpacing(10);

    QPushButton *btnAnnuler = new QPushButton("Annuler");
    btnAnnuler->setFixedHeight(38);
    btnAnnuler->setStyleSheet(
        "QPushButton { background: white; color: #546E7A; "
        "border: 1px solid #B0BEC5; border-radius: 8px; "
        "font-size: 13px; font-weight: bold; padding: 0 20px; }"
        "QPushButton:hover { background: #ECEFF1; }");
    connect(btnAnnuler, &QPushButton::clicked, dlg, &QDialog::reject);

    QPushButton *btnConfirmer = new QPushButton("Confirmer");
    btnConfirmer->setFixedHeight(38);
    btnConfirmer->setStyleSheet(
        "QPushButton { background: #263238; color: white; "
        "border-radius: 8px; font-size: 13px; font-weight: bold; "
        "padding: 0 20px; border: none; }"
        "QPushButton:hover { background: #37474F; }");

    connect(btnConfirmer, &QPushButton::clicked, dlg, [=]() {
        if (Etmp.attribuerEmplacement(idEmp, idBateau)) {
            dlg->accept();
            afficherEmplacements();
            lancerRecommandation();
            QMessageBox::information(this, "Succès",
                                     QString("Emplacement N°%1 attribué avec succès !\nBateau : %2")
                                         .arg(numero, nomBateau));
        }
    });

    footerL->addWidget(btnAnnuler);
    footerL->addWidget(btnConfirmer);
    root->addWidget(footer);

    dlg->exec();
}

// ════════════════════════════════════════
//  ANALYSE SAISONNIÈRE
// ════════════════════════════════════════
void fishingport::afficherAnalyseSaisonniere()
{
    Etmp.afficherAnalyseSaisonniere(this);
}

