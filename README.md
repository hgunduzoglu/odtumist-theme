# ODTÜMİST WordPress Tema Dönüşümü

İstanbul ODTÜ Mezunları Derneği için geliştirilen bu proje, JS tabanlı referans tasarımın WordPress üzerinde yönetilebilir bir tema yapısına dönüştürülmesini hedefler.

## Proje Amacı

Bu çalışmanın ana amacı:

- Referans arayüz deneyimini korumak
- İçerik yönetimini WordPress yönetim paneline taşımak
- Dernek ekibinin teknik desteğe bağımlı kalmadan sayfa, etkinlik ve çalışma grubu içeriklerini güncelleyebilmesini sağlamak

## Mimari Özet

Repo üç ana parçadan oluşur:

- React/Vite referans uygulaması (kök dizin)
- WordPress tema paketi: `wordpress/odtumist-theme`
- Elementor bootstrap eklentisi: `wordpress/odtumist-elementor-bootstrap`

Bu yapı sayesinde hem referans UI korunur hem de WordPress + Elementor ile sürdürülebilir içerik yönetimi sağlanır.

## Öne Çıkan Yetenekler

- Elementor uyumlu tema altyapısı
- Özel içerik tipleri: `event` (etkinlik), `team` (çalışma grubu)
- Taksonomiler: `event-category`, `team-category`
- Ana sayfa ve iç sayfalar için hazır layout/template parçaları
- Kısa kod ailesi (`odtumist_*`) ile modüler içerik yerleşimi
- Bootstrap eklentisi ile tek ekrandan:
  - sayfa oluşturma
  - örnek içerik tohumlama
  - menü kurma
  - okuma ayarlarını yapılandırma
  - Elementor başlangıç düzeni üretme
- WP-CLI üzerinden otomasyon desteği

## Repo Yapısı

```text
.
├── wordpress/
│   ├── odtumist-theme/
│   ├── odtumist-theme.zip
│   ├── odtumist-elementor-bootstrap/
│   └── odtumist-elementor-bootstrap.zip
├── components/
├── services/
├── App.tsx
├── package.json
└── README.md
```

## Kurulum A (Hızlı - ZIP ile)

Önkoşullar:

- WordPress 6.x
- PHP 5.6+ (tema başlığına göre minimum)
- Elementor (önerilir, zorunlu değil)

Adımlar:

1. WordPress yönetim panelinde tema olarak `wordpress/odtumist-theme.zip` dosyasını yükleyip etkinleştir.
2. Eklenti olarak `wordpress/odtumist-elementor-bootstrap.zip` dosyasını yükleyip etkinleştir.
3. `Araçlar > ODTÜMİST Bootstrap` sayfasına gir.
4. İhtiyaca göre seçenekleri belirleyip temel yapıyı kur.

## Kurulum B (Geliştirici - Kaynak Klasörden)

`wp-content` altında manuel kurulum:

1. `wordpress/odtumist-theme` klasörünü `wp-content/themes/` altına kopyala.
2. `wordpress/odtumist-elementor-bootstrap` klasörünü `wp-content/plugins/` altına kopyala.
3. WordPress panelinden temayı ve eklentiyi etkinleştir.
4. `Araçlar > ODTÜMİST Bootstrap` ekranından başlangıç kurulumunu çalıştır.

## Bootstrap Kullanımı

Yönetim panelinden:

- `Araçlar > ODTÜMİST Bootstrap` ekranından Elementor, menü ve içerik senkron seçenekleriyle kurulum/güncelleme yapılır.

WP-CLI ile:

```bash
wp odtumist bootstrap --elementor=1 --menus=1
```

Sık kullanılan opsiyonlar:

- `--force=1` mevcut seed içerikleri yeniden yazar
- `--full=0` legacy shortcode moduna geçer
- `--sync-groups=1` çalışma grubu kartlarını senkronize eder
- `--sync-events=1` etkinlik kartlarını senkronize eder

## Kısa Kod Ailesi (`odtumist_*`)

Başlıca layout kısa kodları:

- `[odtumist_frontpage]`
- `[odtumist_about_layout]`
- `[odtumist_events_layout]`
- `[odtumist_membership_layout]`
- `[odtumist_solidarity_layout]`
- `[odtumist_contact_layout]`

Sık kullanılan modüler kısa kodlar:

- `[odtumist_events_grid limit="6"]`
- `[odtumist_events_gallery]`
- `[odtumist_working_groups_grid limit="8"]`
- `[odtumist_contact_departments]`
- `[odtumist_contact_map]`
- `[odtumist_contact_form provider="auto"]`
- `[odtumist_social_feed]`

## Referans JS Uygulamasını Yerelde Çalıştırma

Bu repo içindeki React/Vite referansını çalıştırmak için:

```bash
npm install
npm run dev
```

Opsiyonel AI özellikleri için `.env.local` dosyasına:

```bash
API_KEY=your_key_here
```

Diğer scriptler:

```bash
npm run build
npm run preview
```

## Render ve Elementor Davranış Notları

- Tema, sayfa Elementor ile gerçekten inşa edilmişse içeriği Elementor render hattından verir.
- Elementor verisi yoksa veya anlamlı değilse, tema kendi template parçalarına fallback eder.
- Front page ve temel iç sayfa akışları bu hibrit davranışa göre çalışır; bu sayede hem düzenlenebilirlik hem güvenli fallback korunur.

