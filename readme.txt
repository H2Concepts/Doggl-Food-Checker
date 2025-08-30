=== Doggl Food Checker ===
Contributors: dogglteam
Tags: dogs, pets, food, safety, health
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Interaktives Tool zur Überprüfung, ob Lebensmittel für Hunde sicher sind. Mit Risikobewertung, Portionsempfehlungen und Notfall-Hinweisen.

== Description ==

Das Doggl Food Checker Plugin hilft Hundebesitzern dabei, schnell und sicher zu überprüfen, ob ein Lebensmittel für ihren Hund geeignet ist.

**Hauptfunktionen:**

* 🔍 **Intelligente Suche** mit Autocomplete und Synonymerkennung
* 🚦 **Ampel-System** für Risikobewertung (Grün/Gelb/Rot)
* ⚖️ **Portionsrechner** basierend auf Hundegewicht (2-70kg)
* 🚨 **Notfall-Warnungen** mit sofortigen Handlungsanweisungen
* 📤 **Share-Funktion** mit temporären Links
* 📄 **PDF-Export** für Ergebnisse
* ❓ **FAQ-Bereich** mit strukturierten Daten für SEO
* 🌐 **Mehrsprachig** vorbereitet

**Verwendung:**

Füge den Shortcode `[doggl_food_check]` in jeden Beitrag oder jede Seite ein, wo das Tool angezeigt werden soll.

**Für Entwickler:**

* REST API für externe Integrationen
* Anpassbare Templates
* Hook-System für Erweiterungen
* Responsive Design
* Barrierefreie Bedienung (ARIA)

== Installation ==

1. Lade die Plugin-Dateien in das `/wp-content/plugins/doggl-food-checker/` Verzeichnis hoch
2. Aktiviere das Plugin über das 'Plugins' Menü in WordPress
3. Gehe zu 'Lebensmittel' im Admin-Bereich, um Daten zu verwalten
4. Verwende den Shortcode `[doggl_food_check]` auf deiner Seite

== Frequently Asked Questions ==

= Wie füge ich neue Lebensmittel hinzu? =

Gehe im WordPress Admin zu "Lebensmittel" und klicke auf "Neues Lebensmittel hinzufügen". Fülle alle relevanten Felder aus, besonders Status, Kategorie und Begründung.

= Kann ich das Design anpassen? =

Ja, du kannst die CSS-Datei `assets/food-checker.css` anpassen oder eigene Styles in deinem Theme hinzufügen.

= Funktioniert das Plugin mit jedem Theme? =

Ja, das Plugin ist theme-unabhängig und funktioniert mit allen WordPress-Themes.

= Sind die Informationen veterinärmedizinisch geprüft? =

Die Beispieldaten basieren auf allgemein verfügbaren veterinärmedizinischen Informationen. Für den produktiven Einsatz sollten alle Daten von einem Tierarzt überprüft werden.

== Screenshots ==

1. Hauptansicht mit Suchfunktion
2. Ergebnis-Karte mit Risikobewertung
3. Admin-Bereich für Lebensmittel-Verwaltung
4. FAQ-Bereich
5. Geteiltes Ergebnis

== Changelog ==

= 1.0.0 =
* Erste Veröffentlichung
* Grundfunktionen: Suche, Risikobewertung, Portionsrechner
* REST API Implementation
* Share-Funktion mit Token-System
* PDF-Export Vorbereitung
* FAQ mit strukturierten Daten
* Responsive Design
* Barrierefreiheit

== Upgrade Notice ==

= 1.0.0 =
Erste Version des Plugins. Keine Upgrade-Schritte erforderlich.

== API Documentation ==

**REST Endpoints:**

* `POST /wp-json/doggl/v1/food/search` - Suche nach Lebensmitteln
* `POST /wp-json/doggl/v1/food/share` - Erstelle Share-Token
* `POST /wp-json/doggl/v1/food/export` - PDF-Export
* `GET /wp-json/doggl/v1/food/item/{id}` - Einzelnes Lebensmittel abrufen

**Shortcode Parameter:**

* `weight` - Standard-Gewicht (Standard: 15)
* `theme` - Theme-Variante (Standard: default)

Beispiel: `[doggl_food_check weight="20" theme="compact"]`