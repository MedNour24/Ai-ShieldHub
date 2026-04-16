/********************************************************************************
** Form generated from reading UI file 'fishingport.ui'
**
** Created by: Qt User Interface Compiler version 6.7.3
**
** WARNING! All changes made in this file will be lost when recompiling UI file!
********************************************************************************/

#ifndef UI_FISHINGPORT_H
#define UI_FISHINGPORT_H

#include <QtCore/QVariant>
#include <QtWidgets/QApplication>
#include <QtWidgets/QComboBox>
#include <QtWidgets/QDateEdit>
#include <QtWidgets/QFrame>
#include <QtWidgets/QGroupBox>
#include <QtWidgets/QHBoxLayout>
#include <QtWidgets/QHeaderView>
#include <QtWidgets/QLabel>
#include <QtWidgets/QLineEdit>
#include <QtWidgets/QMainWindow>
#include <QtWidgets/QMenuBar>
#include <QtWidgets/QProgressBar>
#include <QtWidgets/QPushButton>
#include <QtWidgets/QRadioButton>
#include <QtWidgets/QSpacerItem>
#include <QtWidgets/QStackedWidget>
#include <QtWidgets/QStatusBar>
#include <QtWidgets/QTabWidget>
#include <QtWidgets/QTableView>
#include <QtWidgets/QTableWidget>
#include <QtWidgets/QTextEdit>
#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QWidget>

QT_BEGIN_NAMESPACE

class Ui_fishingport
{
public:
    QWidget *centralwidget;
    QWidget *sidebar;
    QVBoxLayout *sidebarLayout;
    QLabel *lblMenuTitle;
    QPushButton *btnNavBateaux;
    QPushButton *btnNavQuais;
    QPushButton *btnNavEmplacements;
    QPushButton *btnNavEmployes;
    QPushButton *btnNavSorties;
    QStackedWidget *stackedWidget;
    QWidget *page;
    QStackedWidget *stackedWidget_2;
    QWidget *pageListe;
    QWidget *pageTitleRow;
    QHBoxLayout *pageTitleLayout;
    QLabel *lblTitle;
    QSpacerItem *spacerItem;
    QPushButton *btnAjouter;
    QWidget *toolbar;
    QHBoxLayout *toolbarLayout;
    QLineEdit *lineEdit;
    QLabel *lblFilterStatus;
    QComboBox *comboBox;
    QLabel *lblFilterType;
    QComboBox *comboBoxType;
    QLabel *lblTri;
    QComboBox *comboTri;
    QSpacerItem *spacerItem1;
    QPushButton *pushButton;
    QPushButton *pushButton_2;
    QWidget *tableArea;
    QHBoxLayout *tableAreaLayout;
    QTableView *tableBateaux;
    QWidget *chatWidget;
    QVBoxLayout *chatLayout;
    QLabel *lblChatTitle;
    QTextEdit *txtChatHistory;
    QHBoxLayout *chatInputLayout;
    QLineEdit *txtChatInput;
    QPushButton *btnSendChat;
    QWidget *actionButtons;
    QVBoxLayout *actionButtonsLayout;
    QPushButton *btnConsulter;
    QPushButton *btnModify;
    QPushButton *btnActionStats;
    QPushButton *btnPrediction;
    QPushButton *btnDelete;
    QSpacerItem *spacerItem2;
    QFrame *statsFrame;
    QVBoxLayout *statsLayout;
    QLabel *lblStatsTitle;
    QLabel *lblStatEnMer;
    QLabel *lblStatAQuai;
    QLabel *lblStatMaintenance;
    QLabel *lblStatTotal;
    QWidget *pageFormulaire;
    QWidget *formContainer;
    QLabel *lblFormTitle;
    QFrame *formCard;
    QVBoxLayout *verticalLayout_2;
    QWidget *formRow1;
    QHBoxLayout *formRow1Layout;
    QWidget *fieldCarteGrise;
    QVBoxLayout *fieldCarteGriseLayout;
    QLabel *lblCarteGrise;
    QLineEdit *lineEditNum;
    QWidget *fieldNom;
    QVBoxLayout *fieldNomLayout;
    QLabel *lblNom;
    QLineEdit *lineEditNom;
    QWidget *formRow2;
    QHBoxLayout *formRow2Layout;
    QWidget *fieldDate;
    QVBoxLayout *fieldDateLayout;
    QLabel *lblDate;
    QDateEdit *dateEdit;
    QWidget *formRow3;
    QVBoxLayout *formRow3Layout;
    QLabel *lblStatut;
    QComboBox *comboStatus;
    QWidget *widget;
    QVBoxLayout *verticalLayout;
    QLabel *lblType;
    QComboBox *comboType;
    QWidget *formButtons;
    QHBoxLayout *formButtonsLayout;
    QSpacerItem *spacerItem3;
    QPushButton *btnAnnuler;
    QPushButton *btnEnregistrer;
    QLabel *label;
    QWidget *pageStatistiques;
    QLabel *lblStatsPageTitle;
    QLabel *lblStatsSubtitle;
    QWidget *statsCardsRow;
    QHBoxLayout *statsCardsLayout;
    QFrame *cardEnMer;
    QVBoxLayout *cardEnMerLayout;
    QLabel *lblCardMerIcon;
    QLabel *lblCardMerCount;
    QLabel *lblCardMerLabel;
    QFrame *cardAQuai;
    QVBoxLayout *cardAQuaiLayout;
    QLabel *lblCardQuaiIcon;
    QLabel *lblCardQuaiCount;
    QLabel *lblCardQuaiLabel;
    QFrame *cardMaintenance;
    QVBoxLayout *cardMaintenanceLayout;
    QLabel *lblCardMaintIcon;
    QLabel *lblCardMaintCount;
    QLabel *lblCardMaintLabel;
    QFrame *cardTotal;
    QVBoxLayout *cardTotalLayout;
    QLabel *lblCardTotalIcon;
    QLabel *lblCardTotalCount;
    QLabel *lblCardTotalLabel;
    QLabel *lblStatsTableTitle;
    QTableWidget *tableStats;
    QPushButton *btnRefreshStats;
    QPushButton *btnRetour;
    QLabel *chartLabel;
    QWidget *pagePrediction;
    QLabel *lblPredictionTitle;
    QLabel *lblPredictionSubtitle;
    QFrame *cardPredictionInfo;
    QVBoxLayout *cardPredInfoLayout;
    QLabel *lblPredInfoTitle;
    QLabel *lblPredBoatName;
    QLabel *lblPredBoatType;
    QLabel *lblPredBoatDate;
    QLabel *lblPredBoatAge;
    QLabel *lblPredBoatSorties;
    QFrame *cardPredictionResult;
    QVBoxLayout *cardPredResultLayout;
    QLabel *lblPredResultTitle;
    QWidget *uiJsonContainer;
    QVBoxLayout *uiJsonLayout;
    QLabel *lblPredJsonStatus;
    QLabel *lblPredJsonYears;
    QLabel *lblPredJsonLabelEstim;
    QSpacerItem *verticalSpacer_Pred1;
    QLabel *lblPredJsonHealthScoreLabel;
    QProgressBar *progressBarHealth;
    QSpacerItem *verticalSpacer_Pred2;
    QLabel *lblPredJsonReasoningLabel;
    QTextEdit *txtPredictionResult;
    QPushButton *btnLaunchPrediction;
    QPushButton *btnPredictionRetour;
    QWidget *page_2;
    QTabWidget *tabWidget;
    QWidget *tab;
    QFrame *frame;
    QLabel *label_25;
    QLabel *label_26;
    QLabel *label_27;
    QLabel *label_28;
    QGroupBox *groupBox_2;
    QVBoxLayout *verticalLayout_3;
    QTableWidget *tableWidget_3;
    QGroupBox *groupBox;
    QLabel *label_20;
    QComboBox *comboBox_13;
    QLabel *label_21;
    QLineEdit *lineEdit_2;
    QLabel *label_3;
    QLabel *label_22;
    QComboBox *comboBox_7;
    QLabel *label_23;
    QComboBox *comboBox_8;
    QLabel *label_30;
    QDateEdit *dateEdit_2;
    QLabel *label_4;
    QLabel *label_31;
    QTextEdit *textEdit;
    QLabel *label_24;
    QWidget *horizontalLayoutWidget_4;
    QHBoxLayout *horizontalLayout_7;
    QRadioButton *radioButton_8;
    QRadioButton *radioButton_9;
    QRadioButton *radioButton_10;
    QPushButton *pushButton_14;
    QPushButton *pushButton_15;
    QPushButton *pushButton_16;
    QPushButton *pushButton_17;
    QComboBox *comboBox_9;
    QLineEdit *lineEdit_3;
    QComboBox *comboBox_10;
    QComboBox *comboBox_12;
    QWidget *tab_2;
    QLabel *label_2;
    QFrame *line;
    QLabel *label_5;
    QComboBox *comboBox_11;
    QPushButton *pushButton_19;
    QComboBox *comboBox_14;
    QComboBox *comboBox_15;
    QLabel *label_6;
    QLineEdit *lineEdit_5;
    QWidget *widget_historique;
    QLabel *label_9;
    QLabel *label_10;
    QComboBox *comboBox_16;
    QWidget *widget_1;
    QLabel *label_11;
    QPushButton *pushButton_20;
    QMenuBar *menubar;
    QStatusBar *statusbar;

    void setupUi(QMainWindow *fishingport)
    {
        if (fishingport->objectName().isEmpty())
            fishingport->setObjectName("fishingport");
        fishingport->resize(1569, 962);
        fishingport->setIconSize(QSize(24, 24));
        centralwidget = new QWidget(fishingport);
        centralwidget->setObjectName("centralwidget");
        sidebar = new QWidget(centralwidget);
        sidebar->setObjectName("sidebar");
        sidebar->setGeometry(QRect(9, 70, 251, 861));
        sidebarLayout = new QVBoxLayout(sidebar);
        sidebarLayout->setSpacing(6);
        sidebarLayout->setObjectName("sidebarLayout");
        sidebarLayout->setContentsMargins(12, 20, 12, 20);
        lblMenuTitle = new QLabel(sidebar);
        lblMenuTitle->setObjectName("lblMenuTitle");

        sidebarLayout->addWidget(lblMenuTitle);

        btnNavBateaux = new QPushButton(sidebar);
        btnNavBateaux->setObjectName("btnNavBateaux");
        btnNavBateaux->setCheckable(true);
        btnNavBateaux->setChecked(true);

        sidebarLayout->addWidget(btnNavBateaux);

        btnNavQuais = new QPushButton(sidebar);
        btnNavQuais->setObjectName("btnNavQuais");
        btnNavQuais->setCheckable(true);

        sidebarLayout->addWidget(btnNavQuais);

        btnNavEmplacements = new QPushButton(sidebar);
        btnNavEmplacements->setObjectName("btnNavEmplacements");
        btnNavEmplacements->setCheckable(true);

        sidebarLayout->addWidget(btnNavEmplacements);

        btnNavEmployes = new QPushButton(sidebar);
        btnNavEmployes->setObjectName("btnNavEmployes");
        btnNavEmployes->setCheckable(true);

        sidebarLayout->addWidget(btnNavEmployes);

        btnNavSorties = new QPushButton(sidebar);
        btnNavSorties->setObjectName("btnNavSorties");
        btnNavSorties->setCheckable(true);

        sidebarLayout->addWidget(btnNavSorties);

        stackedWidget = new QStackedWidget(centralwidget);
        stackedWidget->setObjectName("stackedWidget");
        stackedWidget->setGeometry(QRect(260, 0, 1281, 881));
        page = new QWidget();
        page->setObjectName("page");
        stackedWidget_2 = new QStackedWidget(page);
        stackedWidget_2->setObjectName("stackedWidget_2");
        stackedWidget_2->setGeometry(QRect(10, 10, 1241, 841));
        pageListe = new QWidget();
        pageListe->setObjectName("pageListe");
        pageTitleRow = new QWidget(pageListe);
        pageTitleRow->setObjectName("pageTitleRow");
        pageTitleRow->setGeometry(QRect(20, 14, 1200, 71));
        pageTitleLayout = new QHBoxLayout(pageTitleRow);
        pageTitleLayout->setObjectName("pageTitleLayout");
        pageTitleLayout->setContentsMargins(0, 0, 0, 0);
        lblTitle = new QLabel(pageTitleRow);
        lblTitle->setObjectName("lblTitle");

        pageTitleLayout->addWidget(lblTitle);

        spacerItem = new QSpacerItem(40, 20, QSizePolicy::Policy::Expanding, QSizePolicy::Policy::Minimum);

        pageTitleLayout->addItem(spacerItem);

        btnAjouter = new QPushButton(pageTitleRow);
        btnAjouter->setObjectName("btnAjouter");

        pageTitleLayout->addWidget(btnAjouter);

        toolbar = new QWidget(pageListe);
        toolbar->setObjectName("toolbar");
        toolbar->setGeometry(QRect(20, 90, 1200, 71));
        toolbarLayout = new QHBoxLayout(toolbar);
        toolbarLayout->setSpacing(10);
        toolbarLayout->setObjectName("toolbarLayout");
        toolbarLayout->setContentsMargins(10, 0, 10, 0);
        lineEdit = new QLineEdit(toolbar);
        lineEdit->setObjectName("lineEdit");

        toolbarLayout->addWidget(lineEdit);

        lblFilterStatus = new QLabel(toolbar);
        lblFilterStatus->setObjectName("lblFilterStatus");

        toolbarLayout->addWidget(lblFilterStatus);

        comboBox = new QComboBox(toolbar);
        comboBox->addItem(QString());
        comboBox->addItem(QString());
        comboBox->addItem(QString());
        comboBox->addItem(QString());
        comboBox->setObjectName("comboBox");

        toolbarLayout->addWidget(comboBox);

        lblFilterType = new QLabel(toolbar);
        lblFilterType->setObjectName("lblFilterType");

        toolbarLayout->addWidget(lblFilterType);

        comboBoxType = new QComboBox(toolbar);
        comboBoxType->addItem(QString());
        comboBoxType->addItem(QString());
        comboBoxType->addItem(QString());
        comboBoxType->addItem(QString());
        comboBoxType->setObjectName("comboBoxType");

        toolbarLayout->addWidget(comboBoxType);

        lblTri = new QLabel(toolbar);
        lblTri->setObjectName("lblTri");

        toolbarLayout->addWidget(lblTri);

        comboTri = new QComboBox(toolbar);
        comboTri->addItem(QString());
        comboTri->addItem(QString());
        comboTri->addItem(QString());
        comboTri->addItem(QString());
        comboTri->addItem(QString());
        comboTri->setObjectName("comboTri");

        toolbarLayout->addWidget(comboTri);

        spacerItem1 = new QSpacerItem(40, 20, QSizePolicy::Policy::Expanding, QSizePolicy::Policy::Minimum);

        toolbarLayout->addItem(spacerItem1);

        pushButton = new QPushButton(toolbar);
        pushButton->setObjectName("pushButton");

        toolbarLayout->addWidget(pushButton);

        pushButton_2 = new QPushButton(toolbar);
        pushButton_2->setObjectName("pushButton_2");

        toolbarLayout->addWidget(pushButton_2);

        tableArea = new QWidget(pageListe);
        tableArea->setObjectName("tableArea");
        tableArea->setGeometry(QRect(39, 179, 1181, 641));
        tableAreaLayout = new QHBoxLayout(tableArea);
        tableAreaLayout->setSpacing(14);
        tableAreaLayout->setObjectName("tableAreaLayout");
        tableAreaLayout->setContentsMargins(0, 0, 0, 0);
        tableBateaux = new QTableView(tableArea);
        tableBateaux->setObjectName("tableBateaux");

        tableAreaLayout->addWidget(tableBateaux);

        chatWidget = new QWidget(tableArea);
        chatWidget->setObjectName("chatWidget");
        chatWidget->setMinimumSize(QSize(350, 0));
        chatWidget->setMaximumSize(QSize(350, 16777215));
        chatLayout = new QVBoxLayout(chatWidget);
        chatLayout->setObjectName("chatLayout");
        lblChatTitle = new QLabel(chatWidget);
        lblChatTitle->setObjectName("lblChatTitle");
        lblChatTitle->setAlignment(Qt::AlignmentFlag::AlignCenter);

        chatLayout->addWidget(lblChatTitle);

        txtChatHistory = new QTextEdit(chatWidget);
        txtChatHistory->setObjectName("txtChatHistory");
        txtChatHistory->setReadOnly(true);

        chatLayout->addWidget(txtChatHistory);

        chatInputLayout = new QHBoxLayout();
        chatInputLayout->setObjectName("chatInputLayout");
        txtChatInput = new QLineEdit(chatWidget);
        txtChatInput->setObjectName("txtChatInput");

        chatInputLayout->addWidget(txtChatInput);

        btnSendChat = new QPushButton(chatWidget);
        btnSendChat->setObjectName("btnSendChat");

        chatInputLayout->addWidget(btnSendChat);


        chatLayout->addLayout(chatInputLayout);


        tableAreaLayout->addWidget(chatWidget);

        actionButtons = new QWidget(tableArea);
        actionButtons->setObjectName("actionButtons");
        actionButtons->setMinimumSize(QSize(120, 0));
        actionButtons->setMaximumSize(QSize(120, 16777215));
        actionButtonsLayout = new QVBoxLayout(actionButtons);
        actionButtonsLayout->setSpacing(10);
        actionButtonsLayout->setObjectName("actionButtonsLayout");
        actionButtonsLayout->setContentsMargins(0, 0, 0, 0);
        btnConsulter = new QPushButton(actionButtons);
        btnConsulter->setObjectName("btnConsulter");

        actionButtonsLayout->addWidget(btnConsulter);

        btnModify = new QPushButton(actionButtons);
        btnModify->setObjectName("btnModify");

        actionButtonsLayout->addWidget(btnModify);

        btnActionStats = new QPushButton(actionButtons);
        btnActionStats->setObjectName("btnActionStats");

        actionButtonsLayout->addWidget(btnActionStats);

        btnPrediction = new QPushButton(actionButtons);
        btnPrediction->setObjectName("btnPrediction");

        actionButtonsLayout->addWidget(btnPrediction);

        btnDelete = new QPushButton(actionButtons);
        btnDelete->setObjectName("btnDelete");

        actionButtonsLayout->addWidget(btnDelete);

        spacerItem2 = new QSpacerItem(20, 40, QSizePolicy::Policy::Minimum, QSizePolicy::Policy::Expanding);

        actionButtonsLayout->addItem(spacerItem2);

        statsFrame = new QFrame(actionButtons);
        statsFrame->setObjectName("statsFrame");
        statsFrame->setFrameShape(QFrame::Shape::StyledPanel);
        statsLayout = new QVBoxLayout(statsFrame);
        statsLayout->setObjectName("statsLayout");
        statsLayout->setContentsMargins(10, 10, 10, 10);
        lblStatsTitle = new QLabel(statsFrame);
        lblStatsTitle->setObjectName("lblStatsTitle");

        statsLayout->addWidget(lblStatsTitle);

        lblStatEnMer = new QLabel(statsFrame);
        lblStatEnMer->setObjectName("lblStatEnMer");

        statsLayout->addWidget(lblStatEnMer);

        lblStatAQuai = new QLabel(statsFrame);
        lblStatAQuai->setObjectName("lblStatAQuai");

        statsLayout->addWidget(lblStatAQuai);

        lblStatMaintenance = new QLabel(statsFrame);
        lblStatMaintenance->setObjectName("lblStatMaintenance");

        statsLayout->addWidget(lblStatMaintenance);

        lblStatTotal = new QLabel(statsFrame);
        lblStatTotal->setObjectName("lblStatTotal");

        statsLayout->addWidget(lblStatTotal);


        actionButtonsLayout->addWidget(statsFrame);


        tableAreaLayout->addWidget(actionButtons);

        stackedWidget_2->addWidget(pageListe);
        pageFormulaire = new QWidget();
        pageFormulaire->setObjectName("pageFormulaire");
        formContainer = new QWidget(pageFormulaire);
        formContainer->setObjectName("formContainer");
        formContainer->setGeometry(QRect(80, 100, 800, 741));
        lblFormTitle = new QLabel(formContainer);
        lblFormTitle->setObjectName("lblFormTitle");
        lblFormTitle->setGeometry(QRect(9, 9, 200, 20));
        formCard = new QFrame(formContainer);
        formCard->setObjectName("formCard");
        formCard->setGeometry(QRect(10, 50, 581, 641));
        formCard->setFrameShape(QFrame::Shape::StyledPanel);
        verticalLayout_2 = new QVBoxLayout(formCard);
        verticalLayout_2->setObjectName("verticalLayout_2");
        formRow1 = new QWidget(formCard);
        formRow1->setObjectName("formRow1");
        formRow1Layout = new QHBoxLayout(formRow1);
        formRow1Layout->setSpacing(24);
        formRow1Layout->setObjectName("formRow1Layout");
        fieldCarteGrise = new QWidget(formRow1);
        fieldCarteGrise->setObjectName("fieldCarteGrise");
        fieldCarteGriseLayout = new QVBoxLayout(fieldCarteGrise);
        fieldCarteGriseLayout->setObjectName("fieldCarteGriseLayout");
        lblCarteGrise = new QLabel(fieldCarteGrise);
        lblCarteGrise->setObjectName("lblCarteGrise");

        fieldCarteGriseLayout->addWidget(lblCarteGrise);

        lineEditNum = new QLineEdit(fieldCarteGrise);
        lineEditNum->setObjectName("lineEditNum");

        fieldCarteGriseLayout->addWidget(lineEditNum);


        formRow1Layout->addWidget(fieldCarteGrise);

        fieldNom = new QWidget(formRow1);
        fieldNom->setObjectName("fieldNom");
        fieldNomLayout = new QVBoxLayout(fieldNom);
        fieldNomLayout->setObjectName("fieldNomLayout");
        lblNom = new QLabel(fieldNom);
        lblNom->setObjectName("lblNom");

        fieldNomLayout->addWidget(lblNom);

        lineEditNom = new QLineEdit(fieldNom);
        lineEditNom->setObjectName("lineEditNom");

        fieldNomLayout->addWidget(lineEditNom);


        formRow1Layout->addWidget(fieldNom);


        verticalLayout_2->addWidget(formRow1);

        formRow2 = new QWidget(formCard);
        formRow2->setObjectName("formRow2");
        formRow2Layout = new QHBoxLayout(formRow2);
        formRow2Layout->setSpacing(24);
        formRow2Layout->setObjectName("formRow2Layout");
        fieldDate = new QWidget(formRow2);
        fieldDate->setObjectName("fieldDate");
        fieldDateLayout = new QVBoxLayout(fieldDate);
        fieldDateLayout->setObjectName("fieldDateLayout");
        lblDate = new QLabel(fieldDate);
        lblDate->setObjectName("lblDate");

        fieldDateLayout->addWidget(lblDate);

        dateEdit = new QDateEdit(fieldDate);
        dateEdit->setObjectName("dateEdit");

        fieldDateLayout->addWidget(dateEdit);


        formRow2Layout->addWidget(fieldDate);

        formRow3 = new QWidget(formRow2);
        formRow3->setObjectName("formRow3");
        formRow3Layout = new QVBoxLayout(formRow3);
        formRow3Layout->setObjectName("formRow3Layout");
        lblStatut = new QLabel(formRow3);
        lblStatut->setObjectName("lblStatut");

        formRow3Layout->addWidget(lblStatut);

        comboStatus = new QComboBox(formRow3);
        comboStatus->addItem(QString());
        comboStatus->addItem(QString());
        comboStatus->addItem(QString());
        comboStatus->setObjectName("comboStatus");

        formRow3Layout->addWidget(comboStatus);


        formRow2Layout->addWidget(formRow3);


        verticalLayout_2->addWidget(formRow2);

        widget = new QWidget(formCard);
        widget->setObjectName("widget");
        verticalLayout = new QVBoxLayout(widget);
        verticalLayout->setObjectName("verticalLayout");
        lblType = new QLabel(widget);
        lblType->setObjectName("lblType");

        verticalLayout->addWidget(lblType);

        comboType = new QComboBox(widget);
        comboType->addItem(QString());
        comboType->addItem(QString());
        comboType->addItem(QString());
        comboType->setObjectName("comboType");

        verticalLayout->addWidget(comboType);


        verticalLayout_2->addWidget(widget);

        formButtons = new QWidget(formCard);
        formButtons->setObjectName("formButtons");
        formButtonsLayout = new QHBoxLayout(formButtons);
        formButtonsLayout->setSpacing(12);
        formButtonsLayout->setObjectName("formButtonsLayout");
        spacerItem3 = new QSpacerItem(40, 20, QSizePolicy::Policy::Expanding, QSizePolicy::Policy::Minimum);

        formButtonsLayout->addItem(spacerItem3);

        btnAnnuler = new QPushButton(formButtons);
        btnAnnuler->setObjectName("btnAnnuler");

        formButtonsLayout->addWidget(btnAnnuler);

        btnEnregistrer = new QPushButton(formButtons);
        btnEnregistrer->setObjectName("btnEnregistrer");

        formButtonsLayout->addWidget(btnEnregistrer);


        verticalLayout_2->addWidget(formButtons);

        label = new QLabel(pageFormulaire);
        label->setObjectName("label");
        label->setGeometry(QRect(160, 40, 391, 61));
        QFont font;
        font.setPointSize(20);
        font.setBold(true);
        font.setItalic(true);
        label->setFont(font);
        stackedWidget_2->addWidget(pageFormulaire);
        pageStatistiques = new QWidget();
        pageStatistiques->setObjectName("pageStatistiques");
        lblStatsPageTitle = new QLabel(pageStatistiques);
        lblStatsPageTitle->setObjectName("lblStatsPageTitle");
        lblStatsPageTitle->setGeometry(QRect(30, 20, 600, 40));
        lblStatsSubtitle = new QLabel(pageStatistiques);
        lblStatsSubtitle->setObjectName("lblStatsSubtitle");
        lblStatsSubtitle->setGeometry(QRect(30, 58, 600, 22));
        statsCardsRow = new QWidget(pageStatistiques);
        statsCardsRow->setObjectName("statsCardsRow");
        statsCardsRow->setGeometry(QRect(30, 110, 1170, 150));
        statsCardsLayout = new QHBoxLayout(statsCardsRow);
        statsCardsLayout->setSpacing(20);
        statsCardsLayout->setObjectName("statsCardsLayout");
        statsCardsLayout->setContentsMargins(0, 0, 0, 0);
        cardEnMer = new QFrame(statsCardsRow);
        cardEnMer->setObjectName("cardEnMer");
        cardEnMer->setFrameShape(QFrame::Shape::StyledPanel);
        cardEnMerLayout = new QVBoxLayout(cardEnMer);
        cardEnMerLayout->setObjectName("cardEnMerLayout");
        cardEnMerLayout->setContentsMargins(20, 20, 20, 20);
        lblCardMerIcon = new QLabel(cardEnMer);
        lblCardMerIcon->setObjectName("lblCardMerIcon");

        cardEnMerLayout->addWidget(lblCardMerIcon);

        lblCardMerCount = new QLabel(cardEnMer);
        lblCardMerCount->setObjectName("lblCardMerCount");

        cardEnMerLayout->addWidget(lblCardMerCount);

        lblCardMerLabel = new QLabel(cardEnMer);
        lblCardMerLabel->setObjectName("lblCardMerLabel");

        cardEnMerLayout->addWidget(lblCardMerLabel);


        statsCardsLayout->addWidget(cardEnMer);

        cardAQuai = new QFrame(statsCardsRow);
        cardAQuai->setObjectName("cardAQuai");
        cardAQuai->setFrameShape(QFrame::Shape::StyledPanel);
        cardAQuaiLayout = new QVBoxLayout(cardAQuai);
        cardAQuaiLayout->setObjectName("cardAQuaiLayout");
        cardAQuaiLayout->setContentsMargins(20, 20, 20, 20);
        lblCardQuaiIcon = new QLabel(cardAQuai);
        lblCardQuaiIcon->setObjectName("lblCardQuaiIcon");

        cardAQuaiLayout->addWidget(lblCardQuaiIcon);

        lblCardQuaiCount = new QLabel(cardAQuai);
        lblCardQuaiCount->setObjectName("lblCardQuaiCount");

        cardAQuaiLayout->addWidget(lblCardQuaiCount);

        lblCardQuaiLabel = new QLabel(cardAQuai);
        lblCardQuaiLabel->setObjectName("lblCardQuaiLabel");

        cardAQuaiLayout->addWidget(lblCardQuaiLabel);


        statsCardsLayout->addWidget(cardAQuai);

        cardMaintenance = new QFrame(statsCardsRow);
        cardMaintenance->setObjectName("cardMaintenance");
        cardMaintenance->setFrameShape(QFrame::Shape::StyledPanel);
        cardMaintenanceLayout = new QVBoxLayout(cardMaintenance);
        cardMaintenanceLayout->setObjectName("cardMaintenanceLayout");
        cardMaintenanceLayout->setContentsMargins(20, 20, 20, 20);
        lblCardMaintIcon = new QLabel(cardMaintenance);
        lblCardMaintIcon->setObjectName("lblCardMaintIcon");

        cardMaintenanceLayout->addWidget(lblCardMaintIcon);

        lblCardMaintCount = new QLabel(cardMaintenance);
        lblCardMaintCount->setObjectName("lblCardMaintCount");

        cardMaintenanceLayout->addWidget(lblCardMaintCount);

        lblCardMaintLabel = new QLabel(cardMaintenance);
        lblCardMaintLabel->setObjectName("lblCardMaintLabel");

        cardMaintenanceLayout->addWidget(lblCardMaintLabel);


        statsCardsLayout->addWidget(cardMaintenance);

        cardTotal = new QFrame(statsCardsRow);
        cardTotal->setObjectName("cardTotal");
        cardTotal->setFrameShape(QFrame::Shape::StyledPanel);
        cardTotalLayout = new QVBoxLayout(cardTotal);
        cardTotalLayout->setObjectName("cardTotalLayout");
        cardTotalLayout->setContentsMargins(20, 20, 20, 20);
        lblCardTotalIcon = new QLabel(cardTotal);
        lblCardTotalIcon->setObjectName("lblCardTotalIcon");

        cardTotalLayout->addWidget(lblCardTotalIcon);

        lblCardTotalCount = new QLabel(cardTotal);
        lblCardTotalCount->setObjectName("lblCardTotalCount");

        cardTotalLayout->addWidget(lblCardTotalCount);

        lblCardTotalLabel = new QLabel(cardTotal);
        lblCardTotalLabel->setObjectName("lblCardTotalLabel");

        cardTotalLayout->addWidget(lblCardTotalLabel);


        statsCardsLayout->addWidget(cardTotal);

        lblStatsTableTitle = new QLabel(pageStatistiques);
        lblStatsTableTitle->setObjectName("lblStatsTableTitle");
        lblStatsTableTitle->setGeometry(QRect(30, 285, 400, 25));
        tableStats = new QTableWidget(pageStatistiques);
        if (tableStats->columnCount() < 2)
            tableStats->setColumnCount(2);
        if (tableStats->rowCount() < 4)
            tableStats->setRowCount(4);
        tableStats->setObjectName("tableStats");
        tableStats->setGeometry(QRect(30, 320, 500, 180));
        tableStats->setEditTriggers(QAbstractItemView::EditTrigger::NoEditTriggers);
        tableStats->setAlternatingRowColors(true);
        tableStats->setSelectionBehavior(QAbstractItemView::SelectionBehavior::SelectRows);
        tableStats->setShowGrid(false);
        tableStats->setRowCount(4);
        tableStats->setColumnCount(2);
        btnRefreshStats = new QPushButton(pageStatistiques);
        btnRefreshStats->setObjectName("btnRefreshStats");
        btnRefreshStats->setGeometry(QRect(30, 520, 180, 40));
        btnRetour = new QPushButton(pageStatistiques);
        btnRetour->setObjectName("btnRetour");
        btnRetour->setGeometry(QRect(230, 520, 180, 40));
        chartLabel = new QLabel(pageStatistiques);
        chartLabel->setObjectName("chartLabel");
        chartLabel->setGeometry(QRect(560, 320, 600, 250));
        chartLabel->setAlignment(Qt::AlignmentFlag::AlignCenter);
        stackedWidget_2->addWidget(pageStatistiques);
        pagePrediction = new QWidget();
        pagePrediction->setObjectName("pagePrediction");
        lblPredictionTitle = new QLabel(pagePrediction);
        lblPredictionTitle->setObjectName("lblPredictionTitle");
        lblPredictionTitle->setGeometry(QRect(30, 20, 800, 40));
        lblPredictionSubtitle = new QLabel(pagePrediction);
        lblPredictionSubtitle->setObjectName("lblPredictionSubtitle");
        lblPredictionSubtitle->setGeometry(QRect(30, 58, 800, 22));
        cardPredictionInfo = new QFrame(pagePrediction);
        cardPredictionInfo->setObjectName("cardPredictionInfo");
        cardPredictionInfo->setGeometry(QRect(30, 100, 540, 200));
        cardPredictionInfo->setFrameShape(QFrame::Shape::StyledPanel);
        cardPredInfoLayout = new QVBoxLayout(cardPredictionInfo);
        cardPredInfoLayout->setObjectName("cardPredInfoLayout");
        cardPredInfoLayout->setContentsMargins(20, 15, 20, 15);
        lblPredInfoTitle = new QLabel(cardPredictionInfo);
        lblPredInfoTitle->setObjectName("lblPredInfoTitle");

        cardPredInfoLayout->addWidget(lblPredInfoTitle);

        lblPredBoatName = new QLabel(cardPredictionInfo);
        lblPredBoatName->setObjectName("lblPredBoatName");

        cardPredInfoLayout->addWidget(lblPredBoatName);

        lblPredBoatType = new QLabel(cardPredictionInfo);
        lblPredBoatType->setObjectName("lblPredBoatType");

        cardPredInfoLayout->addWidget(lblPredBoatType);

        lblPredBoatDate = new QLabel(cardPredictionInfo);
        lblPredBoatDate->setObjectName("lblPredBoatDate");

        cardPredInfoLayout->addWidget(lblPredBoatDate);

        lblPredBoatAge = new QLabel(cardPredictionInfo);
        lblPredBoatAge->setObjectName("lblPredBoatAge");

        cardPredInfoLayout->addWidget(lblPredBoatAge);

        lblPredBoatSorties = new QLabel(cardPredictionInfo);
        lblPredBoatSorties->setObjectName("lblPredBoatSorties");

        cardPredInfoLayout->addWidget(lblPredBoatSorties);

        cardPredictionResult = new QFrame(pagePrediction);
        cardPredictionResult->setObjectName("cardPredictionResult");
        cardPredictionResult->setGeometry(QRect(590, 100, 580, 420));
        cardPredictionResult->setFrameShape(QFrame::Shape::StyledPanel);
        cardPredResultLayout = new QVBoxLayout(cardPredictionResult);
        cardPredResultLayout->setObjectName("cardPredResultLayout");
        cardPredResultLayout->setContentsMargins(20, 15, 20, 15);
        lblPredResultTitle = new QLabel(cardPredictionResult);
        lblPredResultTitle->setObjectName("lblPredResultTitle");

        cardPredResultLayout->addWidget(lblPredResultTitle);

        uiJsonContainer = new QWidget(cardPredictionResult);
        uiJsonContainer->setObjectName("uiJsonContainer");
        uiJsonLayout = new QVBoxLayout(uiJsonContainer);
        uiJsonLayout->setObjectName("uiJsonLayout");
        lblPredJsonStatus = new QLabel(uiJsonContainer);
        lblPredJsonStatus->setObjectName("lblPredJsonStatus");
        QFont font1;
        font1.setPointSize(16);
        font1.setBold(true);
        lblPredJsonStatus->setFont(font1);
        lblPredJsonStatus->setAlignment(Qt::AlignmentFlag::AlignCenter);

        uiJsonLayout->addWidget(lblPredJsonStatus);

        lblPredJsonYears = new QLabel(uiJsonContainer);
        lblPredJsonYears->setObjectName("lblPredJsonYears");
        QFont font2;
        font2.setPointSize(30);
        font2.setBold(true);
        lblPredJsonYears->setFont(font2);
        lblPredJsonYears->setAlignment(Qt::AlignmentFlag::AlignCenter);

        uiJsonLayout->addWidget(lblPredJsonYears);

        lblPredJsonLabelEstim = new QLabel(uiJsonContainer);
        lblPredJsonLabelEstim->setObjectName("lblPredJsonLabelEstim");
        lblPredJsonLabelEstim->setAlignment(Qt::AlignmentFlag::AlignCenter);

        uiJsonLayout->addWidget(lblPredJsonLabelEstim);

        verticalSpacer_Pred1 = new QSpacerItem(20, 12, QSizePolicy::Policy::Minimum, QSizePolicy::Policy::Fixed);

        uiJsonLayout->addItem(verticalSpacer_Pred1);

        lblPredJsonHealthScoreLabel = new QLabel(uiJsonContainer);
        lblPredJsonHealthScoreLabel->setObjectName("lblPredJsonHealthScoreLabel");

        uiJsonLayout->addWidget(lblPredJsonHealthScoreLabel);

        progressBarHealth = new QProgressBar(uiJsonContainer);
        progressBarHealth->setObjectName("progressBarHealth");
        progressBarHealth->setValue(0);
        progressBarHealth->setTextVisible(true);

        uiJsonLayout->addWidget(progressBarHealth);

        verticalSpacer_Pred2 = new QSpacerItem(20, 12, QSizePolicy::Policy::Minimum, QSizePolicy::Policy::Fixed);

        uiJsonLayout->addItem(verticalSpacer_Pred2);

        lblPredJsonReasoningLabel = new QLabel(uiJsonContainer);
        lblPredJsonReasoningLabel->setObjectName("lblPredJsonReasoningLabel");

        uiJsonLayout->addWidget(lblPredJsonReasoningLabel);

        txtPredictionResult = new QTextEdit(uiJsonContainer);
        txtPredictionResult->setObjectName("txtPredictionResult");
        txtPredictionResult->setReadOnly(true);

        uiJsonLayout->addWidget(txtPredictionResult);


        cardPredResultLayout->addWidget(uiJsonContainer);

        btnLaunchPrediction = new QPushButton(pagePrediction);
        btnLaunchPrediction->setObjectName("btnLaunchPrediction");
        btnLaunchPrediction->setGeometry(QRect(30, 320, 250, 45));
        btnPredictionRetour = new QPushButton(pagePrediction);
        btnPredictionRetour->setObjectName("btnPredictionRetour");
        btnPredictionRetour->setGeometry(QRect(300, 320, 180, 45));
        stackedWidget_2->addWidget(pagePrediction);
        stackedWidget->addWidget(page);
        page_2 = new QWidget();
        page_2->setObjectName("page_2");
        tabWidget = new QTabWidget(page_2);
        tabWidget->setObjectName("tabWidget");
        tabWidget->setGeometry(QRect(10, 10, 1291, 781));
        tab = new QWidget();
        tab->setObjectName("tab");
        frame = new QFrame(tab);
        frame->setObjectName("frame");
        frame->setGeometry(QRect(580, 640, 661, 61));
        frame->setFrameShape(QFrame::Shape::StyledPanel);
        frame->setFrameShadow(QFrame::Shadow::Raised);
        label_25 = new QLabel(frame);
        label_25->setObjectName("label_25");
        label_25->setGeometry(QRect(10, 10, 131, 41));
        label_26 = new QLabel(frame);
        label_26->setObjectName("label_26");
        label_26->setGeometry(QRect(160, 10, 141, 41));
        label_27 = new QLabel(frame);
        label_27->setObjectName("label_27");
        label_27->setGeometry(QRect(320, 10, 151, 41));
        label_28 = new QLabel(frame);
        label_28->setObjectName("label_28");
        label_28->setGeometry(QRect(490, 10, 191, 41));
        groupBox_2 = new QGroupBox(tab);
        groupBox_2->setObjectName("groupBox_2");
        groupBox_2->setGeometry(QRect(570, 100, 681, 531));
        verticalLayout_3 = new QVBoxLayout(groupBox_2);
        verticalLayout_3->setObjectName("verticalLayout_3");
        tableWidget_3 = new QTableWidget(groupBox_2);
        if (tableWidget_3->columnCount() < 8)
            tableWidget_3->setColumnCount(8);
        QTableWidgetItem *__qtablewidgetitem = new QTableWidgetItem();
        tableWidget_3->setHorizontalHeaderItem(0, __qtablewidgetitem);
        QTableWidgetItem *__qtablewidgetitem1 = new QTableWidgetItem();
        tableWidget_3->setHorizontalHeaderItem(1, __qtablewidgetitem1);
        QTableWidgetItem *__qtablewidgetitem2 = new QTableWidgetItem();
        tableWidget_3->setHorizontalHeaderItem(2, __qtablewidgetitem2);
        QTableWidgetItem *__qtablewidgetitem3 = new QTableWidgetItem();
        tableWidget_3->setHorizontalHeaderItem(3, __qtablewidgetitem3);
        QTableWidgetItem *__qtablewidgetitem4 = new QTableWidgetItem();
        tableWidget_3->setHorizontalHeaderItem(4, __qtablewidgetitem4);
        QTableWidgetItem *__qtablewidgetitem5 = new QTableWidgetItem();
        tableWidget_3->setHorizontalHeaderItem(5, __qtablewidgetitem5);
        QTableWidgetItem *__qtablewidgetitem6 = new QTableWidgetItem();
        tableWidget_3->setHorizontalHeaderItem(6, __qtablewidgetitem6);
        QTableWidgetItem *__qtablewidgetitem7 = new QTableWidgetItem();
        tableWidget_3->setHorizontalHeaderItem(7, __qtablewidgetitem7);
        tableWidget_3->setObjectName("tableWidget_3");
        tableWidget_3->setEditTriggers(QAbstractItemView::EditTrigger::NoEditTriggers);
        tableWidget_3->setAlternatingRowColors(true);
        tableWidget_3->setSelectionMode(QAbstractItemView::SelectionMode::SingleSelection);
        tableWidget_3->setSelectionBehavior(QAbstractItemView::SelectionBehavior::SelectRows);
        tableWidget_3->setSortingEnabled(true);

        verticalLayout_3->addWidget(tableWidget_3);

        groupBox = new QGroupBox(tab);
        groupBox->setObjectName("groupBox");
        groupBox->setGeometry(QRect(10, 10, 541, 721));
        label_20 = new QLabel(groupBox);
        label_20->setObjectName("label_20");
        label_20->setGeometry(QRect(10, 50, 161, 31));
        comboBox_13 = new QComboBox(groupBox);
        comboBox_13->addItem(QString());
        comboBox_13->addItem(QString());
        comboBox_13->addItem(QString());
        comboBox_13->addItem(QString());
        comboBox_13->addItem(QString());
        comboBox_13->addItem(QString());
        comboBox_13->addItem(QString());
        comboBox_13->addItem(QString());
        comboBox_13->setObjectName("comboBox_13");
        comboBox_13->setGeometry(QRect(10, 90, 191, 41));
        label_21 = new QLabel(groupBox);
        label_21->setObjectName("label_21");
        label_21->setGeometry(QRect(380, 50, 161, 31));
        lineEdit_2 = new QLineEdit(groupBox);
        lineEdit_2->setObjectName("lineEdit_2");
        lineEdit_2->setGeometry(QRect(360, 90, 151, 41));
        label_3 = new QLabel(groupBox);
        label_3->setObjectName("label_3");
        label_3->setGeometry(QRect(360, 140, 141, 20));
        label_22 = new QLabel(groupBox);
        label_22->setObjectName("label_22");
        label_22->setGeometry(QRect(10, 150, 81, 51));
        comboBox_7 = new QComboBox(groupBox);
        comboBox_7->setObjectName("comboBox_7");
        comboBox_7->setGeometry(QRect(10, 200, 191, 41));
        label_23 = new QLabel(groupBox);
        label_23->setObjectName("label_23");
        label_23->setGeometry(QRect(360, 150, 151, 51));
        comboBox_8 = new QComboBox(groupBox);
        comboBox_8->setObjectName("comboBox_8");
        comboBox_8->setGeometry(QRect(320, 200, 191, 41));
        label_30 = new QLabel(groupBox);
        label_30->setObjectName("label_30");
        label_30->setGeometry(QRect(10, 260, 211, 41));
        dateEdit_2 = new QDateEdit(groupBox);
        dateEdit_2->setObjectName("dateEdit_2");
        dateEdit_2->setGeometry(QRect(90, 310, 351, 41));
        label_4 = new QLabel(groupBox);
        label_4->setObjectName("label_4");
        label_4->setGeometry(QRect(100, 360, 341, 20));
        label_31 = new QLabel(groupBox);
        label_31->setObjectName("label_31");
        label_31->setGeometry(QRect(10, 380, 151, 51));
        textEdit = new QTextEdit(groupBox);
        textEdit->setObjectName("textEdit");
        textEdit->setGeometry(QRect(60, 430, 421, 91));
        label_24 = new QLabel(groupBox);
        label_24->setObjectName("label_24");
        label_24->setGeometry(QRect(10, 510, 81, 51));
        horizontalLayoutWidget_4 = new QWidget(groupBox);
        horizontalLayoutWidget_4->setObjectName("horizontalLayoutWidget_4");
        horizontalLayoutWidget_4->setGeometry(QRect(20, 560, 491, 71));
        horizontalLayout_7 = new QHBoxLayout(horizontalLayoutWidget_4);
        horizontalLayout_7->setObjectName("horizontalLayout_7");
        horizontalLayout_7->setContentsMargins(0, 0, 0, 0);
        radioButton_8 = new QRadioButton(horizontalLayoutWidget_4);
        radioButton_8->setObjectName("radioButton_8");
        radioButton_8->setChecked(false);

        horizontalLayout_7->addWidget(radioButton_8);

        radioButton_9 = new QRadioButton(horizontalLayoutWidget_4);
        radioButton_9->setObjectName("radioButton_9");

        horizontalLayout_7->addWidget(radioButton_9);

        radioButton_10 = new QRadioButton(horizontalLayoutWidget_4);
        radioButton_10->setObjectName("radioButton_10");
        radioButton_10->setChecked(true);

        horizontalLayout_7->addWidget(radioButton_10);

        pushButton_14 = new QPushButton(groupBox);
        pushButton_14->setObjectName("pushButton_14");
        pushButton_14->setGeometry(QRect(10, 640, 121, 41));
        pushButton_15 = new QPushButton(groupBox);
        pushButton_15->setObjectName("pushButton_15");
        pushButton_15->setGeometry(QRect(140, 640, 141, 41));
        pushButton_16 = new QPushButton(groupBox);
        pushButton_16->setObjectName("pushButton_16");
        pushButton_16->setGeometry(QRect(290, 640, 100, 41));
        pushButton_17 = new QPushButton(groupBox);
        pushButton_17->setObjectName("pushButton_17");
        pushButton_17->setGeometry(QRect(395, 640, 141, 41));
        comboBox_9 = new QComboBox(tab);
        comboBox_9->addItem(QString());
        comboBox_9->addItem(QString());
        comboBox_9->addItem(QString());
        comboBox_9->addItem(QString());
        comboBox_9->addItem(QString());
        comboBox_9->addItem(QString());
        comboBox_9->setObjectName("comboBox_9");
        comboBox_9->setGeometry(QRect(580, 20, 161, 41));
        lineEdit_3 = new QLineEdit(tab);
        lineEdit_3->setObjectName("lineEdit_3");
        lineEdit_3->setGeometry(QRect(750, 20, 131, 41));
        comboBox_10 = new QComboBox(tab);
        comboBox_10->addItem(QString());
        comboBox_10->addItem(QString());
        comboBox_10->addItem(QString());
        comboBox_10->addItem(QString());
        comboBox_10->addItem(QString());
        comboBox_10->addItem(QString());
        comboBox_10->addItem(QString());
        comboBox_10->setObjectName("comboBox_10");
        comboBox_10->setGeometry(QRect(1080, 20, 131, 41));
        comboBox_12 = new QComboBox(tab);
        comboBox_12->addItem(QString());
        comboBox_12->addItem(QString());
        comboBox_12->addItem(QString());
        comboBox_12->setObjectName("comboBox_12");
        comboBox_12->setGeometry(QRect(910, 20, 151, 41));
        tabWidget->addTab(tab, QString());
        tab_2 = new QWidget();
        tab_2->setObjectName("tab_2");
        label_2 = new QLabel(tab_2);
        label_2->setObjectName("label_2");
        label_2->setGeometry(QRect(10, 40, 291, 31));
        line = new QFrame(tab_2);
        line->setObjectName("line");
        line->setGeometry(QRect(610, 90, 20, 561));
        line->setFrameShape(QFrame::Shape::VLine);
        line->setFrameShadow(QFrame::Shadow::Sunken);
        label_5 = new QLabel(tab_2);
        label_5->setObjectName("label_5");
        label_5->setGeometry(QRect(650, 40, 361, 31));
        comboBox_11 = new QComboBox(tab_2);
        comboBox_11->addItem(QString());
        comboBox_11->addItem(QString());
        comboBox_11->addItem(QString());
        comboBox_11->addItem(QString());
        comboBox_11->addItem(QString());
        comboBox_11->setObjectName("comboBox_11");
        comboBox_11->setGeometry(QRect(1000, 30, 151, 41));
        pushButton_19 = new QPushButton(tab_2);
        pushButton_19->setObjectName("pushButton_19");
        pushButton_19->setGeometry(QRect(480, 30, 101, 31));
        comboBox_14 = new QComboBox(tab_2);
        comboBox_14->addItem(QString());
        comboBox_14->addItem(QString());
        comboBox_14->addItem(QString());
        comboBox_14->setObjectName("comboBox_14");
        comboBox_14->setGeometry(QRect(300, 30, 161, 41));
        comboBox_15 = new QComboBox(tab_2);
        comboBox_15->addItem(QString());
        comboBox_15->addItem(QString());
        comboBox_15->setObjectName("comboBox_15");
        comboBox_15->setGeometry(QRect(30, 100, 161, 31));
        label_6 = new QLabel(tab_2);
        label_6->setObjectName("label_6");
        label_6->setGeometry(QRect(290, 110, 141, 21));
        lineEdit_5 = new QLineEdit(tab_2);
        lineEdit_5->setObjectName("lineEdit_5");
        lineEdit_5->setGeometry(QRect(470, 110, 121, 21));
        widget_historique = new QWidget(tab_2);
        widget_historique->setObjectName("widget_historique");
        widget_historique->setGeometry(QRect(20, 160, 581, 551));
        label_9 = new QLabel(tab_2);
        label_9->setObjectName("label_9");
        label_9->setGeometry(QRect(700, 100, 291, 20));
        label_10 = new QLabel(tab_2);
        label_10->setObjectName("label_10");
        label_10->setGeometry(QRect(640, 150, 71, 20));
        comboBox_16 = new QComboBox(tab_2);
        comboBox_16->setObjectName("comboBox_16");
        comboBox_16->setGeometry(QRect(740, 140, 471, 31));
        widget_1 = new QWidget(tab_2);
        widget_1->setObjectName("widget_1");
        widget_1->setGeometry(QRect(640, 200, 611, 291));
        label_11 = new QLabel(tab_2);
        label_11->setObjectName("label_11");
        label_11->setGeometry(QRect(710, 540, 191, 20));
        pushButton_20 = new QPushButton(tab_2);
        pushButton_20->setObjectName("pushButton_20");
        pushButton_20->setGeometry(QRect(800, 610, 301, 41));
        tabWidget->addTab(tab_2, QString());
        stackedWidget->addWidget(page_2);
        fishingport->setCentralWidget(centralwidget);
        menubar = new QMenuBar(fishingport);
        menubar->setObjectName("menubar");
        menubar->setGeometry(QRect(0, 0, 1569, 26));
        fishingport->setMenuBar(menubar);
        statusbar = new QStatusBar(fishingport);
        statusbar->setObjectName("statusbar");
        fishingport->setStatusBar(statusbar);

        retranslateUi(fishingport);

        stackedWidget->setCurrentIndex(0);
        stackedWidget_2->setCurrentIndex(0);
        tabWidget->setCurrentIndex(0);


        QMetaObject::connectSlotsByName(fishingport);
    } // setupUi

    void retranslateUi(QMainWindow *fishingport)
    {
        fishingport->setWindowTitle(QCoreApplication::translate("fishingport", "Smart Fishing Port - Gestion Portuaire", nullptr));
        lblMenuTitle->setText(QCoreApplication::translate("fishingport", "NAVIGATION", nullptr));
        btnNavBateaux->setText(QCoreApplication::translate("fishingport", "Gestion des Bateaux", nullptr));
        btnNavQuais->setText(QCoreApplication::translate("fishingport", "Gestion des Quais", nullptr));
        btnNavEmplacements->setText(QCoreApplication::translate("fishingport", "Gestion des Emplacements", nullptr));
        btnNavEmployes->setText(QCoreApplication::translate("fishingport", "Gestion des Employes", nullptr));
        btnNavSorties->setText(QCoreApplication::translate("fishingport", "Gestion des Sorties", nullptr));
        lblTitle->setText(QCoreApplication::translate("fishingport", "Gestion des Bateaux", nullptr));
        btnAjouter->setText(QCoreApplication::translate("fishingport", "+ Ajouter un Bateau", nullptr));
        lineEdit->setPlaceholderText(QCoreApplication::translate("fishingport", "Rechercher par nom, carte grise...", nullptr));
        lblFilterStatus->setText(QCoreApplication::translate("fishingport", "Statut :", nullptr));
        comboBox->setItemText(0, QCoreApplication::translate("fishingport", "Tous les statuts", nullptr));
        comboBox->setItemText(1, QCoreApplication::translate("fishingport", "En mer", nullptr));
        comboBox->setItemText(2, QCoreApplication::translate("fishingport", "A quai", nullptr));
        comboBox->setItemText(3, QCoreApplication::translate("fishingport", "En maintenance", nullptr));

        lblFilterType->setText(QCoreApplication::translate("fishingport", "Type :", nullptr));
        comboBoxType->setItemText(0, QCoreApplication::translate("fishingport", "Tous les types", nullptr));
        comboBoxType->setItemText(1, QCoreApplication::translate("fishingport", "Petit", nullptr));
        comboBoxType->setItemText(2, QCoreApplication::translate("fishingport", "Moyen", nullptr));
        comboBoxType->setItemText(3, QCoreApplication::translate("fishingport", "Grand", nullptr));

        lblTri->setText(QCoreApplication::translate("fishingport", "Trier par :", nullptr));
        comboTri->setItemText(0, QCoreApplication::translate("fishingport", "Nom (A-Z)", nullptr));
        comboTri->setItemText(1, QCoreApplication::translate("fishingport", "Nom (Z-A)", nullptr));
        comboTri->setItemText(2, QCoreApplication::translate("fishingport", "Statut", nullptr));
        comboTri->setItemText(3, QCoreApplication::translate("fishingport", "Date d'ajout (recent)", nullptr));
        comboTri->setItemText(4, QCoreApplication::translate("fishingport", "Date d'ajout (ancien)", nullptr));

        pushButton->setText(QCoreApplication::translate("fishingport", "Exporter CSV", nullptr));
        pushButton_2->setText(QCoreApplication::translate("fishingport", "Actualiser", nullptr));
        lblChatTitle->setText(QCoreApplication::translate("fishingport", "<b>Assistant IA (Mistral)</b>", nullptr));
        txtChatInput->setPlaceholderText(QCoreApplication::translate("fishingport", "Posez une question sur les bateaux...", nullptr));
        btnSendChat->setText(QCoreApplication::translate("fishingport", "Envoyer", nullptr));
        btnConsulter->setText(QCoreApplication::translate("fishingport", "Consulter", nullptr));
        btnModify->setText(QCoreApplication::translate("fishingport", "Modifier", nullptr));
        btnActionStats->setText(QCoreApplication::translate("fishingport", "Statistiques", nullptr));
        btnPrediction->setText(QCoreApplication::translate("fishingport", "Pr\303\251diction IA", nullptr));
        btnDelete->setText(QCoreApplication::translate("fishingport", "Supprimer", nullptr));
        lblStatsTitle->setText(QCoreApplication::translate("fishingport", "STATISTIQUES", nullptr));
        lblStatEnMer->setText(QCoreApplication::translate("fishingport", "En mer : --", nullptr));
        lblStatAQuai->setText(QCoreApplication::translate("fishingport", "A quai : --", nullptr));
        lblStatMaintenance->setText(QCoreApplication::translate("fishingport", "En maintenance : --", nullptr));
        lblStatTotal->setText(QCoreApplication::translate("fishingport", "Total : --", nullptr));
        lblFormTitle->setText(QString());
        lblCarteGrise->setText(QCoreApplication::translate("fishingport", "Numero Carte Grise *", nullptr));
        lineEditNum->setPlaceholderText(QCoreApplication::translate("fishingport", "ex. TN-2024-001", nullptr));
        lblNom->setText(QCoreApplication::translate("fishingport", "Nom du Bateau *", nullptr));
        lineEditNom->setPlaceholderText(QCoreApplication::translate("fishingport", "ex. Al Bahr", nullptr));
        lblDate->setText(QCoreApplication::translate("fishingport", "Date de Fabrication", nullptr));
        lblStatut->setText(QCoreApplication::translate("fishingport", "Statut *", nullptr));
        comboStatus->setItemText(0, QCoreApplication::translate("fishingport", "En mer", nullptr));
        comboStatus->setItemText(1, QCoreApplication::translate("fishingport", "En maintenance", nullptr));
        comboStatus->setItemText(2, QCoreApplication::translate("fishingport", "A quai", nullptr));

        lblType->setText(QCoreApplication::translate("fishingport", "Type de Bateau *", nullptr));
        comboType->setItemText(0, QCoreApplication::translate("fishingport", "Petit", nullptr));
        comboType->setItemText(1, QCoreApplication::translate("fishingport", "Moyen", nullptr));
        comboType->setItemText(2, QCoreApplication::translate("fishingport", "Grand", nullptr));

        btnAnnuler->setText(QCoreApplication::translate("fishingport", "Annuler", nullptr));
        btnEnregistrer->setText(QCoreApplication::translate("fishingport", "Enregistrer", nullptr));
        label->setText(QCoreApplication::translate("fishingport", "Informations du Bateau", nullptr));
        lblStatsPageTitle->setText(QCoreApplication::translate("fishingport", "Statistiques de la Flotte", nullptr));
        lblStatsSubtitle->setText(QCoreApplication::translate("fishingport", "Repartition des bateaux selon leur statut actuel", nullptr));
        lblCardMerIcon->setText(QCoreApplication::translate("fishingport", "En mer", nullptr));
        lblCardMerCount->setText(QCoreApplication::translate("fishingport", "--", nullptr));
        lblCardMerLabel->setText(QCoreApplication::translate("fishingport", "bateaux", nullptr));
        lblCardQuaiIcon->setText(QCoreApplication::translate("fishingport", "A quai", nullptr));
        lblCardQuaiCount->setText(QCoreApplication::translate("fishingport", "--", nullptr));
        lblCardQuaiLabel->setText(QCoreApplication::translate("fishingport", "bateaux", nullptr));
        lblCardMaintIcon->setText(QCoreApplication::translate("fishingport", "En maintenance", nullptr));
        lblCardMaintCount->setText(QCoreApplication::translate("fishingport", "--", nullptr));
        lblCardMaintLabel->setText(QCoreApplication::translate("fishingport", "bateaux", nullptr));
        lblCardTotalIcon->setText(QCoreApplication::translate("fishingport", "Total flotte", nullptr));
        lblCardTotalCount->setText(QCoreApplication::translate("fishingport", "--", nullptr));
        lblCardTotalLabel->setText(QCoreApplication::translate("fishingport", "bateaux", nullptr));
        lblStatsTableTitle->setText(QCoreApplication::translate("fishingport", "Detail par Statut", nullptr));
        btnRefreshStats->setText(QCoreApplication::translate("fishingport", "Actualiser les stats", nullptr));
        btnRetour->setText(QCoreApplication::translate("fishingport", "Annuler / Retour", nullptr));
        chartLabel->setText(QString());
        lblPredictionTitle->setText(QCoreApplication::translate("fishingport", "Prediction de Duree de Vie - Intelligence Artificielle", nullptr));
        lblPredictionSubtitle->setText(QCoreApplication::translate("fishingport", "Estimation basee sur la date de fabrication, le type et le nombre de sorties", nullptr));
        lblPredInfoTitle->setText(QCoreApplication::translate("fishingport", "Informations du Bateau Selectionne", nullptr));
        lblPredBoatName->setText(QCoreApplication::translate("fishingport", "Nom : --", nullptr));
        lblPredBoatType->setText(QCoreApplication::translate("fishingport", "Type : --", nullptr));
        lblPredBoatDate->setText(QCoreApplication::translate("fishingport", "Date de fabrication : --", nullptr));
        lblPredBoatAge->setText(QCoreApplication::translate("fishingport", "Age : --", nullptr));
        lblPredBoatSorties->setText(QCoreApplication::translate("fishingport", "Nombre de sorties : --", nullptr));
        lblPredResultTitle->setText(QCoreApplication::translate("fishingport", "Resultat de la Prediction IA (Mistral)", nullptr));
        lblPredJsonStatus->setText(QCoreApplication::translate("fishingport", "\303\211tat G\303\251n\303\251ral: En attente...", nullptr));
        lblPredJsonYears->setStyleSheet(QCoreApplication::translate("fishingport", "color: #00B4D8;", nullptr));
        lblPredJsonYears->setText(QCoreApplication::translate("fishingport", "-- ans", nullptr));
        lblPredJsonLabelEstim->setStyleSheet(QCoreApplication::translate("fishingport", "color: #7C9CBF; font-size: 11px;", nullptr));
        lblPredJsonLabelEstim->setText(QCoreApplication::translate("fishingport", "Dur\303\251e de vie restante estim\303\251e", nullptr));
        lblPredJsonHealthScoreLabel->setStyleSheet(QCoreApplication::translate("fishingport", "font-weight: bold; color: #A8C5DA;", nullptr));
        lblPredJsonHealthScoreLabel->setText(QCoreApplication::translate("fishingport", "Score de Sant\303\251 du Bateau :", nullptr));
        progressBarHealth->setStyleSheet(QCoreApplication::translate("fishingport", "QProgressBar{border:1px solid rgba(0,180,216,0.30);border-radius:6px;text-align:center;background-color:#0A1628;color:#FFFFFF;font-weight:bold;}QProgressBar::chunk{background-color:#2DC653;border-radius:5px;}", nullptr));
        progressBarHealth->setFormat(QCoreApplication::translate("fishingport", "%p%", nullptr));
        lblPredJsonReasoningLabel->setStyleSheet(QCoreApplication::translate("fishingport", "font-weight: bold; color: #A8C5DA;", nullptr));
        lblPredJsonReasoningLabel->setText(QCoreApplication::translate("fishingport", "Analyse D\303\251taill\303\251e :", nullptr));
        txtPredictionResult->setPlaceholderText(QCoreApplication::translate("fishingport", "Cliquez sur Lancer la Prediction pour obtenir l'analyse IA...", nullptr));
        btnLaunchPrediction->setText(QCoreApplication::translate("fishingport", "Lancer la Prediction", nullptr));
        btnPredictionRetour->setText(QCoreApplication::translate("fishingport", "Retour", nullptr));
        label_25->setText(QCoreApplication::translate("fishingport", "\360\237\223\212Total:", nullptr));
        label_26->setText(QCoreApplication::translate("fishingport", "\360\237\237\242Libres :", nullptr));
        label_27->setText(QCoreApplication::translate("fishingport", "\360\237\224\264Occup\303\251s :", nullptr));
        label_28->setText(QCoreApplication::translate("fishingport", "\360\237\237\241Maintenance :", nullptr));
        groupBox_2->setTitle(QCoreApplication::translate("fishingport", "Liste des Emplacements", nullptr));
        QTableWidgetItem *___qtablewidgetitem = tableWidget_3->horizontalHeaderItem(0);
        ___qtablewidgetitem->setText(QCoreApplication::translate("fishingport", "ID", nullptr));
        QTableWidgetItem *___qtablewidgetitem1 = tableWidget_3->horizontalHeaderItem(1);
        ___qtablewidgetitem1->setText(QCoreApplication::translate("fishingport", "Type", nullptr));
        QTableWidgetItem *___qtablewidgetitem2 = tableWidget_3->horizontalHeaderItem(2);
        ___qtablewidgetitem2->setText(QCoreApplication::translate("fishingport", "Num\303\251ro", nullptr));
        QTableWidgetItem *___qtablewidgetitem3 = tableWidget_3->horizontalHeaderItem(3);
        ___qtablewidgetitem3->setText(QCoreApplication::translate("fishingport", "Quai", nullptr));
        QTableWidgetItem *___qtablewidgetitem4 = tableWidget_3->horizontalHeaderItem(4);
        ___qtablewidgetitem4->setText(QCoreApplication::translate("fishingport", "\303\211tat", nullptr));
        QTableWidgetItem *___qtablewidgetitem5 = tableWidget_3->horizontalHeaderItem(5);
        ___qtablewidgetitem5->setText(QCoreApplication::translate("fishingport", "Bateau Affect\303\251", nullptr));
        QTableWidgetItem *___qtablewidgetitem6 = tableWidget_3->horizontalHeaderItem(6);
        ___qtablewidgetitem6->setText(QCoreApplication::translate("fishingport", "Date D\303\251but", nullptr));
        QTableWidgetItem *___qtablewidgetitem7 = tableWidget_3->horizontalHeaderItem(7);
        ___qtablewidgetitem7->setText(QCoreApplication::translate("fishingport", "Action", nullptr));
        groupBox->setTitle(QCoreApplication::translate("fishingport", "Formulaire Emplacement", nullptr));
        label_20->setText(QCoreApplication::translate("fishingport", "Type Emplacement :", nullptr));
        comboBox_13->setItemText(0, QCoreApplication::translate("fishingport", "Aucun type", nullptr));
        comboBox_13->setItemText(1, QCoreApplication::translate("fishingport", "P\303\252che artisanale", nullptr));
        comboBox_13->setItemText(2, QCoreApplication::translate("fishingport", "Commerce", nullptr));
        comboBox_13->setItemText(3, QCoreApplication::translate("fishingport", "Plaisance l\303\251g\303\250re", nullptr));
        comboBox_13->setItemText(4, QCoreApplication::translate("fishingport", "Plaisance lourde", nullptr));
        comboBox_13->setItemText(5, QCoreApplication::translate("fishingport", "Mixte p\303\252che-transport", nullptr));
        comboBox_13->setItemText(6, QCoreApplication::translate("fishingport", "Sp\303\251cial Maintenance", nullptr));
        comboBox_13->setItemText(7, QCoreApplication::translate("fishingport", "Urgence", nullptr));

        label_21->setText(QCoreApplication::translate("fishingport", "Num\303\251ro * :", nullptr));
        label_3->setText(QString());
        label_22->setText(QCoreApplication::translate("fishingport", "Quai * :", nullptr));
        label_23->setText(QCoreApplication::translate("fishingport", "Bateau Affect\303\251 :", nullptr));
        label_30->setText(QCoreApplication::translate("fishingport", "Date de d\303\251but d'occupation:", nullptr));
        label_4->setText(QString());
        label_31->setText(QCoreApplication::translate("fishingport", "Commentaires :", nullptr));
        label_24->setText(QCoreApplication::translate("fishingport", "\303\211tat * :", nullptr));
        radioButton_8->setText(QCoreApplication::translate("fishingport", "\360\237\237\242 Libre", nullptr));
        radioButton_9->setText(QCoreApplication::translate("fishingport", "\360\237\224\264 Occup\303\251", nullptr));
        radioButton_10->setText(QCoreApplication::translate("fishingport", "\360\237\237\241 Maintenance", nullptr));
        pushButton_14->setText(QCoreApplication::translate("fishingport", "\342\234\205 Ajouter", nullptr));
        pushButton_15->setText(QCoreApplication::translate("fishingport", "\342\234\217\357\270\217 Confirmer Modif", nullptr));
        pushButton_16->setText(QCoreApplication::translate("fishingport", "\342\235\214 Annuler", nullptr));
        pushButton_17->setText(QCoreApplication::translate("fishingport", "\360\237\244\226 Commentaire IA", nullptr));
        comboBox_9->setItemText(0, QCoreApplication::translate("fishingport", "\360\237\224\215Rechercher par:", nullptr));
        comboBox_9->setItemText(1, QCoreApplication::translate("fishingport", "type", nullptr));
        comboBox_9->setItemText(2, QCoreApplication::translate("fishingport", "id", nullptr));
        comboBox_9->setItemText(3, QCoreApplication::translate("fishingport", "etat", nullptr));
        comboBox_9->setItemText(4, QCoreApplication::translate("fishingport", "num\303\251ro", nullptr));
        comboBox_9->setItemText(5, QCoreApplication::translate("fishingport", "Dur\303\251e", nullptr));

        lineEdit_3->setPlaceholderText(QCoreApplication::translate("fishingport", "Rechercher...", nullptr));
        comboBox_10->setItemText(0, QCoreApplication::translate("fishingport", "trier par:", nullptr));
        comboBox_10->setItemText(1, QCoreApplication::translate("fishingport", "\342\206\221 ID", nullptr));
        comboBox_10->setItemText(2, QCoreApplication::translate("fishingport", "\342\206\223 ID", nullptr));
        comboBox_10->setItemText(3, QCoreApplication::translate("fishingport", "\342\206\221 \303\211tat", nullptr));
        comboBox_10->setItemText(4, QCoreApplication::translate("fishingport", "\342\206\223 \303\211tat", nullptr));
        comboBox_10->setItemText(5, QCoreApplication::translate("fishingport", "\342\206\221 Bateau affect\303\251", nullptr));
        comboBox_10->setItemText(6, QCoreApplication::translate("fishingport", "\342\206\223 Bateau affect\303\251", nullptr));

        comboBox_12->setItemText(0, QCoreApplication::translate("fishingport", "\360\237\223\204 Exporter par:", nullptr));
        comboBox_12->setItemText(1, QCoreApplication::translate("fishingport", "PDF", nullptr));
        comboBox_12->setItemText(2, QCoreApplication::translate("fishingport", "Excel", nullptr));

        tabWidget->setTabText(tabWidget->indexOf(tab), QCoreApplication::translate("fishingport", "Accueil", nullptr));
        label_2->setText(QCoreApplication::translate("fishingport", "\360\237\223\234 Historique des emplacements:", nullptr));
        label_5->setText(QCoreApplication::translate("fishingport", "\360\237\223\212les Statistiques d'emplacement selon:", nullptr));
        comboBox_11->setItemText(0, QCoreApplication::translate("fishingport", "Voir stats par:", nullptr));
        comboBox_11->setItemText(1, QCoreApplication::translate("fishingport", "\303\251tat", nullptr));
        comboBox_11->setItemText(2, QCoreApplication::translate("fishingport", "type", nullptr));
        comboBox_11->setItemText(3, QCoreApplication::translate("fishingport", "num", nullptr));
        comboBox_11->setItemText(4, QCoreApplication::translate("fishingport", "quai", nullptr));

        pushButton_19->setText(QCoreApplication::translate("fishingport", "Consulter", nullptr));
        comboBox_14->setItemText(0, QCoreApplication::translate("fishingport", "tout l'historique", nullptr));
        comboBox_14->setItemText(1, QCoreApplication::translate("fishingport", "ce mois", nullptr));
        comboBox_14->setItemText(2, QCoreApplication::translate("fishingport", "cette semaine", nullptr));

        comboBox_15->setItemText(0, QCoreApplication::translate("fishingport", "R\303\251cent en premier", nullptr));
        comboBox_15->setItemText(1, QCoreApplication::translate("fishingport", "Ancien en premier", nullptr));

        label_6->setText(QCoreApplication::translate("fishingport", "\360\237\224\215Rechercher....", nullptr));
        lineEdit_5->setPlaceholderText(QCoreApplication::translate("fishingport", "Rechercher...", nullptr));
        label_9->setText(QCoreApplication::translate("fishingport", "\360\237\247\240Moteur de recommandation IA:", nullptr));
        label_10->setText(QCoreApplication::translate("fishingport", "Bateau:", nullptr));
        label_11->setText(QCoreApplication::translate("fishingport", "\360\237\223\205Analyse saisonni\303\250re:", nullptr));
        pushButton_20->setText(QCoreApplication::translate("fishingport", "Analyse de tendances saisonni\303\250res", nullptr));
        tabWidget->setTabText(tabWidget->indexOf(tab_2), QCoreApplication::translate("fishingport", "statistiques & historiques", nullptr));
    } // retranslateUi

};

namespace Ui {
    class fishingport: public Ui_fishingport {};
} // namespace Ui

QT_END_NAMESPACE

#endif // UI_FISHINGPORT_H
