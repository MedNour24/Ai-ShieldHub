#include "fishingport.h"
#include "connexion.h"
#include <QApplication>

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);

    Connexion c;
    if (!c.ouvrirConnexion())
        return -1;

    fishingport w;
    w.show();

    return a.exec();
}
