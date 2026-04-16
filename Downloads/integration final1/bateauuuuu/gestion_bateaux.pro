QT += core gui widgets sql network printsupport charts

greaterThan(QT_MAJOR_VERSION, 4): QT += widgets

CONFIG += c++17

SOURCES += \
    main.cpp \
    connexion.cpp \
    bateau.cpp \
    emplacement.cpp \
    fishingport.cpp

HEADERS += \
    connexion.h \
    bateau.h \
    emplacement.h \
    fishingport.h

FORMS += \
    fishingport.ui

# Default rules for deployment
qnx: target.path = /tmp/${TARGET}/bin
else: unix:!android: target.path = /opt/${TARGET}/bin
!isEmpty(target.path): INSTALLS += target
