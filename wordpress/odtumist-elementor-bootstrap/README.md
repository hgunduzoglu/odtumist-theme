# ODTUMIST Elementor Bootstrap

Bu eklenti, ODTUMIST kurulumunu tek ekrandan otomatiklestirir.

## Neler Yapar

- Sayfalari olusturur: `anasayfa`, `hakkimizda`, `etkinlikler`, `uyelik`, `dayanisma`, `iletisim`, `haberler`
- Alt sayfalari olusturur:
  - `hakkimizda` altinda: `neler-yapiyoruz`, `calisma-gruplarimiz`, `sen-de-katil`, `tarihce`, `yonetim`
  - `uyelik` altinda: `neden-uye-olmaliyim`, `bilgi-guncelleme`, `aidat-odeme`, `uyelik-avantajlari`
  - `dayanisma` altinda: `networking`, `burs`, `maraton`, `mentorluk`, `bursiyerler`, `gonulluler`, `bagiscilar`, `paydaslar`
- CPT/taksonomi kurar:
  - `event` + `event-category`
  - `team` + `team-category`
- Ornek etkinlik ve calisma grubu icerikleri ekler
- Menu yerlestirmeleri yapar (primary/footer lokasyonlari)
- Okuma ayarlarini yapar (statik anasayfa + haberler)
- Permalink yapisini `/%postname%/` yapar
- Elementor aktifse:
  - Elementor temel ayarlarini uygular
  - Elementor duzenleme destegini `page`, `post`, `event`, `team` tiplerine acar
  - Sayfalara Elementor ile duzenlenebilir baslangic iskeleti yazar
  - `Tam Elementor Modu` aciksa sayfalari shortcode yerine gercek widget/section olarak kurar
  - Istenirse calisma grubu kartlarini (Anasayfa + Hakkimizda) mevcut icerigi ezmeden Elementor kartlarina senkronize eder

## Onemli Davranis

- Varsayilan mod: **mevcut icerikleri ezmez**
- "Zorla Yeniden Tohumla" acik ise sayfa/CPT/Elementor icerikleri tekrar yazilir
- Menuler sadece bossa otomatik kurulur; istersen checkbox ile yeniden kurabilirsin
- "Tam Elementor Modu" aciksa slider, kartlar, bloklar Elementor icerisinde tek tek tasinabilir/duzenlenebilir olur

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
- `--full=0` legacy shortcode moduna duser (tam duzenlenebilir mod kapanir)
- `--menus=1` menuleri yeniden kurar
- `--cleanup=0` ornek icerik temizligini atlar
- `--sync-groups=1` calisma grubu kart bloklarini mevcut team iceriginden gunceller

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
