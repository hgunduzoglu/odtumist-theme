# ODTUMIST Elementor Bootstrap

Bu eklenti, ODTUMIST kurulumunu tek ekrandan otomatiklestirir.

## Neler Yapar

- Sayfalari olusturur: `anasayfa`, `hakkimizda`, `etkinlikler`, `uyelik`, `dayanisma`, `iletisim`, `haberler`
- CPT/taksonomi kurar:
  - `event` + `event-category`
  - `team` + `team-category`
- Ornek etkinlik ve calisma grubu icerikleri ekler
- Menu yerlestirmeleri yapar (primary/footer lokasyonlari)
- Okuma ayarlarini yapar (statik anasayfa + haberler)
- Permalink yapisini `/%postname%/` yapar
- Elementor aktifse:
  - Elementor temel ayarlarini uygular
  - Sayfalara Elementor ile duzenlenebilir baslangic iskeleti yazar

## Onemli Davranis

- Varsayilan mod: **mevcut icerikleri ezmez**
- "Zorla Yeniden Tohumla" acik ise sayfa/CPT/Elementor icerikleri tekrar yazilir
- Menuler sadece bossa otomatik kurulur; istersen checkbox ile yeniden kurabilirsin

## Kullanim

1. Eklentiyi zipleyip WordPress'e yukle.
2. Eklentiyi aktif et.
3. `Araclar > ODTUMIST Bootstrap` sayfasina gir.
4. Ihtiyacina gore checkbox'lari sec.
5. `Temel Yapiyi Kur / Guncelle` butonuna bas.

## WP-CLI ile Tek Komut

`wp odtumist bootstrap --elementor=1 --menus=1`

Opsiyonlar:
- `--force=1` mevcut tohumlanmis icerikleri yeniden yazar
- `--elementor=0` Elementor iskeleti yazimini kapatir
- `--menus=1` menuleri yeniden kurar
- `--cleanup=0` ornek icerik temizligini atlar

## Elementor icin hazir kisa kodlar

- `[odtumist_frontpage]`
- `[odtumist_about_layout]`
- `[odtumist_events_layout]`
- `[odtumist_membership_layout]`
- `[odtumist_solidarity_layout]`
- `[odtumist_contact_layout]`
- `[odtumist_events_grid limit="6"]`
- `[odtumist_working_groups_grid limit="8"]`
- `[odtumist_contact_departments]`
- `[odtumist_contact_map]`
- `[odtumist_contact_form provider="auto"]`
