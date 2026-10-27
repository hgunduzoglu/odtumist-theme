# ODTÜMİST yurtdışı Bursmetre backend'i

WordPress'ten ayrı çalışan, Docker ve PM2 gerektirmeyen Node.js servisidir. API ve tarayıcı kullanan veri toplayıcı ayrı `systemd` süreçleri olarak çalışır. Tarayıcı yalnızca zamanlanmış veri toplama sırasında açılır ve kapanır. API ziyaretçiler için tarayıcı başlatmaz.

## 27 Ekim 2026 kurulum durumu

- Sunucu: `ssh edutrip`, Ubuntu 26.04 LTS, x86_64, 4 vCPU / 16 GB RAM.
- Node.js **24.22.1 LTS**, npm, Git, GitHub CLI, Nginx, Certbot kuruldu.
- Playwright **1.64.1**, Chromium headless shell ve Linux bağımlılıkları kuruldu.
- Chromium sandbox'ı `bursmetre` kullanıcısıyla doğrulandı. Sistem genelindeki kullanıcı namespace / AppArmor korumaları kapatılmadı; yalnızca kurulu Chromium çalıştırılabilir dosyasına namespace izni eklendi.
- `odtumist` deploy kullanıcısı ve `bursmetre` servis kullanıcısı oluşturuldu.
- `odtumist.edutrip.ai` için HTTPS sertifikası ve otomatik yenileme hazırlandı.
- Uygulama servis dosyaları hazır, **henüz etkinleştirilmedi**. GitHub bağlantısı ve uygulamanın canlıya alınması sonraki aşama.
- `https://odtumist.edutrip.ai/bootstrap-healthz` host hazırlığını gösterir. Diğer yollar uygulama kurulana kadar açıkça `503 / not_deployed` döndürür.

**Önemli kaynak kısıtı:** Give Lively, sunucunun hem HTTP hem de gerçek Chromium isteğine `403` erişim kontrolü döndürüyor. Tarayıcı kurulumu çalışıyor; canlı kampanyaların okunması henüz doğrulanamadı. Ayrıştırıcı sentetik HTML/JSON örnekleriyle test edildi. Erişim sağlandığında gerçek DOM ile ayrıca doğrulanmalıdır. Kod CAPTCHA çözmez, proxy/stealth kullanmaz veya erişim kontrolünü aşmaya çalışmaz. Kaynak engelliyken boş veriyi gerçek bağış tutarı gibi yayımlamaz.

## Kodun yapısı

| Dosya | Görevi |
|---|---|
| `src/givelively.js` | İzole ve sandbox'lı Chromium açar; gözlemlenen üye bağlantılarını/sayfalamayı takip eder. |
| `src/givelively-parser.js` | Takım toplamını ve kampanya listesini ayrı çıkarır; çelişkili/eksik veriyi reddeder. |
| `src/tcmb.js` | Resmî `today.xml` içindeki USD `ForexBuying`, `Unit` ve bülten tarihini okur. |
| `src/collector.js` | İki kaynağı bağımsız günceller; hatalarda son başarılı kayıtları korur. |
| `src/payload.js` | USD tutarlarını son yayımlanan alış kuruyla TL'ye çevirir; 49.500 TL yıllık burs tutarını kullanır. |
| `src/server.js` | Hafif, salt okunur HTTP API. |
| `src/probe.js` | Kaynakları yazma yapmadan test eder. |
| `src/parse-saved.js` | Kaydedilmiş/render edilmiş HTML'i çevrimdışı test eder; canlı veriyi değiştirmez. |

Takım toplamı, kişi kampanyalarının toplamıyla tekrar toplanmaz. Doğrudan takıma yapılan bağışların farkı ayrı tutulur. USD ve TL kuruşlarıyla hesap yapılır; TCMB oranı ondalık tamsayı aritmetiğiyle uygulanır. Para birimi/kur bulunamazsa sabit eski bir kur uydurulmaz. `lastUpdated` bağış verisinin zamanıdır; kurun tarihi ayrıca `fx.date` alanındadır.

## Sunucuya ilk kurulum — uygulanan komutlar

Windows'ta proje klasöründen:

```powershell
scp backend/deploy/bootstrap-ubuntu.sh edutrip:/tmp/odtumist-bootstrap.sh
ssh edutrip "bash /tmp/odtumist-bootstrap.sh"

scp -r backend/deploy edutrip:/tmp/odtumist-deploy-stage
ssh edutrip "bash /tmp/odtumist-deploy-stage/prepare-web.sh"
```

İlk script Ubuntu paketlerini, checksum doğrulamalı Node 24 LTS dağıtımını, hesapları ve tarayıcıyı kurar. Ubuntu'nun otomatik güncellemesi apt kilidini tutuyorsa bekler. İkinci script Nginx, Let's Encrypt HTTPS ve pasif servis dosyalarını hazırlar. İlk kurulumda GitHub'a giriş yapmaz, repo klonlamaz, uygulamayı başlatmaz. `prepare-web.sh` bakım yapılandırmasını yazar; canlı uygulamayı yeniden devreye almak için `activate.sh` kullanılır.

Bootstrap log'u: `/var/log/odtumist-bootstrap.log`. Root SSH anahtarları ve erişim ayarları değiştirilmedi. UFW etkinleştirilmedi. Node API daha sonra yalnızca `127.0.0.1:3100` adresinde dinleyecek; dış erişim Nginx HTTPS üzerinden olacak.

Sertifika e-posta verilmeden alındı. İsterseniz daha sonra sunucuda şu komutla yenileme bildirim adresi ekleyebilirsiniz:

```bash
sudo certbot update_account --email EMAIL_ADRESINIZ
```

## Yerelde çalıştırma

Node.js 24 LTS gerekir. Ana dizindeki React/TypeScript projesini kurmaya gerek yoktur:

```bash
cd backend
npm ci
# .env.example dosyasını .env olarak kopyalayıp gerekirse düzenleyin.
npm test
npx playwright install --only-shell chromium
npm run probe
npm run collect
npm start
```

Linux'ta tarayıcı sistem kütüphaneleri eksikse:

```bash
npx playwright install-deps chromium
```

`collect` kaynak hatasında çıkış kodu 1 verir; hata durumu ve varsa son başarılı veriler `DATA_DIR` içinde korunur. `probe` diske yazmaz. Gerçek sayfanın render edilmiş HTML'i edinildiğinde ayrıştırıcı şu komutla kontrol edilir:

```bash
npm run parse:saved -- /path/to/rendered-team-page.html
```

## GitHub'a gönderme — yerel bilgisayar

Yalnızca backend değişikliklerini ekleyin; çalışma ağacındaki mevcut `bursmetre-odtumist` gitlink silinmesi bu çalışmanın parçası değildir:

```bash
git add backend
git commit -m "Add standalone Bursmetre backend and Ubuntu deployment"
git push origin HEAD
```

## GitHub bağlantısı — kullanıcının yapacağı adım

Sunucuya girip deploy kullanıcısına geçin:

```bash
ssh edutrip
sudo -iu odtumist
gh auth login --hostname github.com --git-protocol https --web
gh auth setup-git
gh auth status
```

`gh auth login` ekranda GitHub cihaz doğrulama kodu ve bağlantı verir. Kendi hesabınızla tamamlayın; erişim token'ını sohbetle paylaşmayın. Repo erişimi doğrulanınca:

```bash
cd /srv/odtumist
gh repo clone hgunduzoglu/odtumist-theme app
exit
```

Beklenen uygulama yolu `/srv/odtumist/app/backend` şeklindedir. SSH `edutrip` hesabı root olduğu için `exit` sizi root oturumuna döndürür.

## Uygulamayı canlıya alma — GitHub bağlantısından sonra

Bu aşama henüz uygulanmadı. Repo çekildikten sonra:

```bash
sudo bash /srv/odtumist/app/backend/deploy/activate.sh
sudo systemctl start odtumist-collector.service
curl -i https://odtumist.edutrip.ai/healthz
curl -i https://odtumist.edutrip.ai/readyz
curl -i https://odtumist.edutrip.ai/api/yurtdisi
```

`activate.sh` bağımlılıkları yükler, testleri çalıştırır, Nginx'i API'ye bağlar ve API ile veri toplama zamanlayıcısını etkinleştirir. Başarılı API yanıtı kaynakların gerçekten okunabildiği anlamına gelmez: `/healthz` süreç sağlığını, `/readyz` doğrulanmış ve güncel veri bulunmasını gösterir.

Give Lively erişimi düzelmeden ilk toplama `source_blocked` ile başarısız olur. Bu durumda `/api/yurtdisi`, veri uydurmak yerine `503 / no_complete_snapshot` döndürür. Daha önceden başarılı veri varsa `200` ile son veri, `stale: true` ve açıklama verilir.

## Servisler ve kaynak sınırları

| Birim | Çalışma | Sınır |
|---|---|---|
| `odtumist-api.service` | Sürekli açık | En fazla 192 MB RAM, bir çekirdeğin %25'i |
| `odtumist-collector.timer` | 15 dakikada bir, küçük rastgele gecikmeyle | Aynı toplayıcı paralel çalışmaz |
| `odtumist-collector.service` | İş bitince kapanır | En fazla 1 GB RAM, bir çekirdeğin %75'i, 120 saniye, düşük CPU/I/O önceliği |

Toplayıcı ile API aynı dosya formatını paylaşır; API veri dizinini salt okunur kullanır. Tarayıcı her denemede kapanır. Manim/FFmpeg projesi için Python, TeX, render servisleri veya sistem FFmpeg kurulumu yapılmadı; bunlar ayrı projenin ihtiyaçlarına göre hazırlanmalıdır. Playwright'ın kendi küçük FFmpeg yardımcısı tarayıcı paketiyle gelmektedir.

```bash
sudo systemctl status odtumist-api.service
sudo systemctl status odtumist-collector.timer
sudo systemctl list-timers odtumist-collector.timer
sudo journalctl -u odtumist-api.service -n 100 --no-pager
sudo journalctl -u odtumist-collector.service -n 100 --no-pager
sudo nginx -t
```

## Yapılandırma ve güncelleme

Canlı yapılandırma `/etc/odtumist-bursmetre.env` dosyasında, kaynak veriler `/var/lib/odtumist-bursmetre` dizininde saklanır. İkisi de GitHub'a gitmez. API anahtarı isteğe bağlıdır; `API_TOKEN` verilirse veri yolları `Authorization: Bearer ...` ister. Anahtarlı API için WordPress tarafına da HTTP header desteği eklenmesi gerekir. Varsayılan API sadece zaten herkese açık kampanya bilgilerini sunar.

```bash
sudo -u odtumist git -C /srv/odtumist/app pull --ff-only
sudo bash /srv/odtumist/app/backend/deploy/activate.sh
```

Güncelleme veri dosyalarını silmez. Node/Playwright sürümleri değiştirilirse browser kurulumu ve ona ait AppArmor profili de bootstrap scriptiyle güncellenmelidir. `prepare-web.sh` canlı uygulama güncellemesi için kullanılmaz.

## API sözleşmesi ve WordPress entegrasyonu

`GET /api/yurtdisi` ve eşdeğer `/api/v1/yurtdisi` yolları aynı veriyi döndürür:

- `totalUsd`, `totalTry`: takımın esas alınan toplamı.
- `directTeamUsd`: takım toplamının kişi kampanyaları toplamını aşan kısmı.
- `campaigns[]`: `id`, `ad`, `url`, `currency`, `raised`, `raisedCents`, `raisedTry`.
- `fx`: `provider`, `url`, `type`, `date`, `fetchedAt`, `usdTryRate`.
- `scholarship`: yıllık burs tutarı ve tam bursu karşılanan öğrenci sayısı.
- `lastUpdated`: son başarılı bağış okuma zamanı.
- `stale`, `warnings`: güncellik ve kaynak hataları.

Mevcut WordPress eklentisi eski sabit kurlarla kişi kampanyalarını topluyor. **Sadece endpoint adresini değiştirmek bu yeni sözleşme için yeterli değildir.** Canlı veri doğrulandıktan sonra eklenti `totalUsd/totalTry/fx` alanlarını esas alacak şekilde ayrıca güncellenecek. Bu aşamada WordPress kaynakları veya kurulum ZIP'i değiştirilmedi.
