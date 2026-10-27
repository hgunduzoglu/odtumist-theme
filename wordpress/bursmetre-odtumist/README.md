# ODTÜMİST Bursmetre — WordPress entegrasyonu

Google Sheets CSV okuma, sütun eşleştirme, toplamlar, sıralamalar, yurtdışı JSON verisini işleme ve önbellek süreleri önceki sürümle aynıdır. Sheet ID/GID ayarları ve eski kısa kodlar korunur.

## Kurulum

1. `wordpress/bursmetre-odtumist.zip` paketini **Eklentiler → Yeni Eklenti Ekle → Eklenti Yükle** üzerinden yükleyip etkinleştirin. Eski Burs Metre eklentisi kuruluysa mevcut sürümün üzerine güncelleyin.
2. **Ayarlar → Burs Metre** ekranında Google Sheets ID/GID ve hedef tutarlarını girin. Tabloların CSV adresi oturum açmadan okunabilmelidir. Önceki sürümden gelen ayarlar otomatik korunur.
3. Eklenti etkinleşirken yayınlanmış `dayanisma-spor-maraton` sayfasını bulur. Bulamazsa sırasıyla `dayanisma/maraton` ve `maraton` sayfalarına bakar. Başka bir sayfa kullanıyorsanız **Maraton Üst Sayfası** alanından seçip ayarları kaydedin.
4. Gerekirse **Bursmetre Sayfalarını Kur / Bağlantıları Güncelle** düğmesine basın. Güncel sitede oluşacak adresler:
   - `/dayanisma-spor-maraton/bursmetre/`
   - `/dayanisma-spor-maraton/bursmetre/yurtdisi/`
5. Sayfa önbelleği kullanılıyorsa maraton ve Bursmetre sayfalarının önbelleğini temizleyin.

Maraton sayfasındaki metninde **Bursmetre** veya **Burs Metre** geçen mevcut bağlantılar, ziyaretçiye gösterilirken yeni pano adresine bağlanır. Elementor içeriği veritabanında yeniden yazılmaz. İsterseniz Elementor butonunun bağlantısını ayarlar ekranında görünen Bursmetre adresine elle de ayarlayabilirsiniz.

Kurulum mevcut maraton içeriğini ve menüleri yeniden oluşturmaz. Kendi oluşturduğu iki sayfayı tekrar üretmez; bu sayfalarda sonradan yaptığınız düzenlemeleri korur. Hedef adreste başka bir sayfa varsa içeriği değiştirmeden durur ve ayarlar ekranında uyarı verir. Üst sayfayı değiştirdikten sonra kurulum düğmesine basılması, eklentinin oluşturduğu sayfaları yeni üst sayfanın altına taşır.

## Yurtdışı kaynak adresi

**Yurtdışı Veri Adresi (JSON API)** alanı boşsa önceki kaynak kullanılır:
`https://odtuluyuz.antpooleuropa.com/api/yurtdisi`

Adres değiştiyse aynı veri yapısını döndüren yeni API adresini bu alana girin. Beklenen yapı değişmedi:

```json
{
  "lastUpdated": "2026-10-22T09:00:00Z",
  "campaigns": [
    {"ad": "Kampanya adı", "url": "https://example.org/kampanya", "raised": 100, "currency": "USD"}
  ]
}
```

Mevcut `bursmetre_yurtdisi_endpoint`, `bursmetre_yurtdisi_rates`, `bursmetre_yurtdisi_page_url` ve `bursmetre_dashboard_home_url` filtreleri çalışmaya devam eder. Öğrenci bursu hesabı ve mevcut döviz dönüşüm oranları değiştirilmedi.

## Kısa kodlar

- `[bursmetre-dashboard]`: yurt içi pano.
- `[bursmetre-yurtdisi]`: yurtdışı pano.
- `[bursmetre_button]`: ziyaretçilere açık pano bağlantısı.
- `[bursmetre_button label="Bursmetre’yi Gör" admin_only="1"]`: isteğe bağlı yönetici bağlantısı.

Kısa kodlar normal WordPress içeriğinde veya Elementor **Shortcode** bileşeninde kullanılabilir. Yeni sayfalar tema header/footer'ıyla açılır. Elementor ile sonradan özel bir düzen hazırlanırsa bu düzen kullanılır. Grafikler bir sayfada birden fazla pano bulunsa da ayrı çalışır.

Eski `/maraton/bursmetre/` ve `/maraton/bursmetre/yurtdisi/` adresleri, hedef sayfalar yayınlandığında 301 ile yeni adreslerine gider. Bursmetre CSS/JS dosyaları pano olan sayfalarda yüklenir. Chart.js 4.6.1 ve MIT lisansı paketin içindedir.

## Yerel doğrulama

PHP 7.4+ ve WordPress 6.0+ gerekir. Testler sadece geçici bir WordPress kurulumunda çalıştırılmalıdır:

1. Geçici kurulumun `wp-config.php` dosyasına `define('BURSMETRE_TEST_MODE', true);` ekleyin.
2. Eklentiyi bu kurulumun `wp-content/plugins/bursmetre-odtumist` klasörüne kopyalayın.
3. `tests/fixtures.php` dosyasını geçici kurulumun `wp-content/mu-plugins` klasörüne kopyalayın. Bu dosya yalnızca örnek veriler sağlar; canlıya yüklenmez.
4. `BURSMETRE_WP_ROOT` ortam değişkenini geçici WordPress klasörüne ayarlayıp `php tests/wordpress-integration.php` çalıştırın.

Testler sayfa hiyerarşisini, tekrar kurulumu, içerik korumayı, hedef adres çakışmalarını, Elementor bağlantısını, koşullu dosya yüklemeyi, birden fazla panoyu ve örnek verilerle önceki hesaplamaları kontrol eder. Kurulum ZIP'i testleri, `.git` klasörünü ve `.bak` dosyalarını içermez.
