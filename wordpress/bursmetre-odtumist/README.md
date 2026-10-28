# ODTÜMİST Bursmetre — WordPress entegrasyonu

1.2.0 sürümünde yurtdışı kampanyaları da Google Sheets'ten okunur. Bağışlar ve kampanya hedefleri, WordPress üzerinden alınan son yayımlanmış TCMB döviz alış kurlarıyla TL'ye çevrilir. Ayrı backend veya API anahtarı gerekmez. Yurt içi veri okuma ve sayfa içerikleri korunur.

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

## Yurtdışı Google Sheets ve hedefler

Varsayılan tablo, gönderilen [yurtdışı kampanyalar Sheet'i](https://docs.google.com/spreadsheets/d/1kj26qQyLsYr2gdaRdx7jf2g7FiGNQITULjPoP6_atcU/edit?gid=0) ve `GID=0` olarak tanımlıdır. ZIP'i mevcut eklentinin üzerine yüklemek yeterlidir. Değiştirmek için **Ayarlar → Burs Metre → Yurtdışı Kampanyalar Sheet ID / Yurtdışı GID** alanlarını kullanın. Eski JSON API adresi artık kullanılmaz.

Her satır bir kampanyadır. İlk sütundaki sıra numarası dikkate alınmaz:

| Kampanya Adı | Toplanan | Hedef | Para Birimi | Link | Resim URL |
|---|---:|---:|---|---|---|
| Örnek kampanya | 1250,50 | 10000 | USD | https://example.org/kampanya | https://example.org/resim.png |

- **Kampanya Adı** ve **Toplanan** zorunludur. Henüz bağış yoksa `0` yazın. Hatalı tutarlar sessizce sıfır kabul edilmez.
- **Hedef** aynı satırdaki para birimindedir. Kartta hedef tutarı ve gerçekleşme yüzdesi gösterilir. Boş bırakılabilir.
- Üst panonun hedefi kampanya hedeflerinin TL karşılıklarının toplamıdır. Hiçbir hedef girilmemişse ayarlardaki **Yurtdışı Yedek Hedef Tutarı (₺)** kullanılır.
- Boş para birimi USD sayılır. TCMB'nin kur yayımlamadığı bir kod TL gibi değerlendirilmez; uyarı ve varsa son başarılı veri gösterilir.
- **Link** ve **Resim URL** hücrelerine doğrudan adres yazın. `IMAGE()` formülü gerekmiyor. Resim boşsa veya yüklenemezse adın ilk harfleri gösterilir.
- `1.250,50`, `1,250.50`, `1250,50` ve `1250.50` tutarları desteklenir. Negatif/bozuk tutarlar ve tekrar eden kampanya bağlantıları uyarı verir.
- Genel toplamı kampanya satırı olarak eklemeyin. Adı `Toplam`, `Genel Toplam`, `Total` veya `Grand Total` olan özet satırlar tekrar sayılmamak için atlanır. Doğrudan takıma yapılan ve kampanyalara dahil olmayan bağışlar için ayrı bir satır kullanılabilir.

## TCMB kuru ve yenileme

[TCMB today.xml](https://www.tcmb.gov.tr/kurlar/today.xml) içindeki `ForexBuying / Unit` kullanılır. Nakit alış veya satış kuru kullanılmaz. Hafta sonu ve tatillerde son yayımlanan bülten geçerlidir; kur tarihi ve kullanılan oran panoda görünür. Kur yoksa eski sabit oranlara dönülmez. Yıllık burs tutarı **49.500 TL** olarak korunur.

Sheet ve pano 10 dakika, kur 1 saat önbelleğe alınır. WordPress zamanlayıcısı trafik geldikçe yaklaşık 15 dakikada bir yenilemeyi dener. Anında yenilemek için **Ayarlar → Burs Metre → Yurtdışı Verilerini ve Kuru Yenile** düğmesine basın. Sayfa/CDN önbelleği kullanılıyorsa ayrıca yurtdışı sayfasını temizleyin.

Sheet veya TCMB geçici olarak okunamazsa son başarılı kayıt korunur ve uyarı gösterilir. Farklı Sheet'lerin ve eski API'nin önbellekleri karışmaz. “Son güncelleme” Sheet'in eklenti tarafından son başarılı okunma zamanıdır; elle yapılan düzenlemenin saati değildir. `bursmetre_yurtdisi_page_url` ve `bursmetre_dashboard_home_url` filtreleri korunur; eski JSON endpoint ve sabit kur filtreleri artık kullanılmaz.

## Kısa kodlar

- `[bursmetre-dashboard]`: yurt içi pano.
- `[bursmetre-yurtdisi]`: yurtdışı pano.
- `[bursmetre_button]`: ziyaretçilere açık pano bağlantısı.
- `[bursmetre_button label="Bursmetre’yi Gör" admin_only="1"]`: isteğe bağlı yönetici bağlantısı.

Kısa kodlar normal WordPress içeriğinde veya Elementor **Shortcode** bileşeninde kullanılabilir. Yeni sayfalar tema header/footer'ıyla açılır. Elementor ile sonradan özel bir düzen hazırlanırsa bu düzen kullanılır. Grafikler bir sayfada birden fazla pano bulunsa da ayrı çalışır.

Eski `/maraton/bursmetre/` ve `/maraton/bursmetre/yurtdisi/` adresleri, hedef sayfalar yayınlandığında 301 ile yeni adreslerine gider. Bursmetre CSS/JS dosyaları pano olan sayfalarda yüklenir. Chart.js 4.6.1 ve MIT lisansı paketin içindedir.

## Yerel doğrulama

PHP 7.4+, SimpleXML ve WordPress 6.0+ gerekir. Veri işleme, hesaplama, hata/önbellek ve HTML çıktısı regresyon kontrolleri WordPress veritabanına dokunmadan PHP CLI ile çalışır:

```bash
php wordpress/tests/yurtdisi-data.php
```

Testler WordPress fonksiyonlarının sınırlı test karşılıklarını kullanır; gerçek hosting, HTTP API, zamanlayıcı ve tema/Elementor entegrasyonu ayrıca sitede denenmelidir. Kurulum ZIP'i testleri veya geçici kaynak verilerini içermez.
