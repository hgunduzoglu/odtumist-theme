# BTF / Give Lively veri erişimi talebi

Takım: https://secure.givelively.org/teams/bridge-to-turkiye/2026-btf-marathon-steps-that-shape-futures/odtumist

## Gereken veri ve erişim

- Yalnızca ODTÜMİST 2026 takımının ID'si, adı, herkese açık adresi, para birimi ve toplam bağış tutarı.
- Takıma bağlı bütün fundraiser/kampanyaların ID'si, kamuya açık görünen adı, adresi ve topladığı tutar; mümkünse hedefi.
- Üye/kampanya sayısı, sayfalama bilgisi ve güncelleme zamanı.
- Doğrudan takıma bağış, iade, iptal, offline bağış ve eşleştirme katkılarının toplamda nasıl yer aldığı. Hangi tutar alanının sayfadaki sayaçla eşleştiği: original/gross/net ve işlem masrafı uygulaması.

Bağışçı adı, e-posta, adres veya ödeme bilgisi gerekmiyor. Ana BTF maraton kampanyasının toplamı ile ODTÜMİST takımının toplamı ayrılmalı. Takım toplamına üye tutarları tekrar eklenmemeli. Kaynak USD tutarını alacağız; TL dönüşümünü backend son yayımlanan TCMB USD döviz alış kuruyla yapacak. 15 dakikada bir güncelleme yeterli, sağlayıcının önerdiği daha düşük sıklığa uyabiliriz.

Tercih sırası:

1. **Desteklenen salt okunur API varsa:** Dokümantasyon, takım/kampanya ID'leri, kimlik doğrulama yöntemi ve yetki kapsamı, sayfalama, tutar birimi (dolar/cent), güncelleme alanları ve istek sınırları istenmeli. Böyle bir API'nin mevcut olduğu henüz doğrulanmadı.
2. **Otomatik JSON/CSV aktarımı:** BTF'nin ürettiği, sadece bu takıma ait özet veriyi sunan ve düzenli okunabilen kaynak yeterli. Takım toplamı ve tüm kampanyaların aynı veri anına ait olması tercih edilir. Manuel CSV başlangıç verisini doğrular; kendi başına otomatik güncelleme sağlamaz.
3. **BTF'nin kendi Zapier entegrasyonu:** BTF bağlantıyı kendi hesabında kurabilir. Takım bazında filtrelenmiş ve kişisel bağışçı verilerinden arındırılmış çıktı, kontrollü bir Sheet veya bizim hazırlayacağımız kimlik doğrulamalı alıcıya aktarılabilir. Alıcı endpoint henüz uygulanmadı. İlk veri aktarımı ile yeni bağış, güncellenen bağış ve ödeme durumu değişiklikleri birlikte kapsanmalı. İadeler ve offline katkılar için toplamla mutabakat yöntemi sorulmalı; sadece yeni bağış tetikleyicisi yeterli değildir.
4. **İzinli sayfa okuma:** BTF/Give Lively uygun görürse `94.237.30.11` IP'si için bot korumasında izin ve önerilen okuma sıklığı istenebilir. Sonrasında parser gerçek sayfa yapısıyla doğrulanmalı.

Give Lively, belgelerindeki Zapier JSON endpoint'lerinin **herkese açık API olmadığını ve özel kodla doğrudan kullanımını desteklemediğini** belirtiyor. Bu nedenle yalnızca “API key gönderin” demek yeterli değil. Zapier kurulumu için Organization ID ve API key kuruluşun Nonprofit Admin Portal'ındaki `Settings → Organization Settings → Integrations → Zapier → Settings` yolunda bulunuyor. Bu anahtarın bizimle paylaşılması yerine bağlantının BTF hesabında kurulması tercih edilir; kuruluş genelindeki anahtarı takım ile sınırlı varsaymamalıyız.

## Sunucudaki deneme — 27 Ekim 2026

- Çıkış IPv4: `94.237.30.11`. Backend alan adı: `odtumist.edutrip.ai`.
- Ubuntu üzerinde hem sandbox açık headless Chromium hem Xvfb üzerinde pencereli Chromium denendi. Proxy, stealth, CAPTCHA çözümü veya aktarılan tarayıcı çerezleri kullanılmadı.
- Doğrudan takım URL'sine HTTP/headless tarayıcı erişimi `403` döndürdü.
- Pencereli Chromium [BTF'nin 2026 maraton sayfasını](https://bridgetoturkiye.org/btf-marathon-annual-campaign-2026/) `200` ile açtı ve görünen “VISIT THE BTF MARATHON CAMPAIGN” bağlantısına tıkladı.
- Bu bağlantı `https://secure.givelively.org/donate/bridge-to-turkiye/2026-btf-marathon-steps-that-shape-futures` adresine gidiyor. Bir ziyarette `200` gözlendi; takım/kampanya tutarları okunup doğrulanmadı. Son ziyarette **27 Ekim 2026 17:59:27 UTC / 20:59:27 Türkiye saati** itibarıyla `403`, `x-datadome: protected` ve görünür CAPTCHA iframe'i doğrulandı; bu noktada gezinme durduruldu.
- Son engellenen isteğin CloudFront referansı: `FejdXM-5DIYjF4KhfZhPiULKCycvIbquZn_Q8U4_LZG-ZQLW5dY5qg==`.
- Son rapor: `/var/lib/odtumist-bursmetre/diagnostics/headed-browser-report.json`. Tarayıcı kapandı; uygulama servisleri etkinleştirilmedi.

Bulgular erişim korumasını gösteriyor; IP'nin kalıcı olarak yasaklandığını veya hiçbir yöntemle erişilemeyeceğini kanıtlamıyor. Canlı takım toplamı ve kampanya listesi henüz doğrulanamadı.

## BTF'ye gönderilebilecek hazır mesaj

Subject: Read-only donation totals for ODTUMIST's 2026 BTF Marathon team

Hello,

We are integrating ODTUMIST's overseas marathon donations into the scholarship meter on odtumist.org. Our team page is:

https://secure.givelively.org/teams/bridge-to-turkiye/2026-btf-marathon-steps-that-shape-futures/odtumist

Could you help us obtain a supported, read-only data source for this team? We need the team's current USD total and the complete list of its member fundraising pages, including each page's ID, public name, URL and amount raised. A last-updated timestamp and member count would also be useful. We do not need donor names, email addresses or payment details.

Our preferred options are a documented API, an automated team-scoped JSON/CSV export, or an integration managed by BTF that supplies these aggregates. If an API is available, could you share its documentation, authentication requirements, team/campaign IDs, pagination and rate limits?

Please also confirm how to reproduce the public team's counter, including direct-to-team donations, refunds, cancellations, offline donations and matching gifts. Which amount fields should we use so that transaction fees are handled consistently with the public counter?

If Zapier is the supported route, could BTF configure the connection and provide team-filtered data, including an initial backfill and subsequent donation/status updates? We understand that Give Lively's documented Zapier endpoints are not a supported public API for direct custom-code access.

Alternatively, if automated reading of the public page is approved, could Give Lively allow our server IP 94.237.30.11 and advise a suitable request frequency? Our backend is hosted at odtumist.edutrip.ai. Standard HTTP and Chromium requests from the server encounter HTTP 403 / DataDome verification, including when following the campaign link from BTF's website. The latest observed block was on October 27, 2026 at 17:59:27 UTC. We can provide the request reference if helpful.

We would refresh no more frequently than once every 15 minutes, or follow your recommended limits. USD-to-TRY conversion will be handled separately using the latest published Central Bank of the Republic of Türkiye buying rate.

Thank you.

## Resmî kaynaklar

- [Give Lively: Zapier kurulumu, tetikleyiciler ve API kullanım sınırı](https://www.givelively.org/resources/get-started-with-give-livelys-zapier-integration)
- [Give Lively: takım sayfası, doğrudan takım bağışı ve üye kampanyaları](https://www.givelively.org/resources/view-a-walkthrough-of-a-team-fundraising-page)
- [Give Lively: rapor/CSV alanları, iade, ödeme durumu ve ID'ler](https://www.givelively.org/resources/explore-the-donor-data-available-for-member-nonprofits)

Bu dosya bir taslaktır; BTF'ye mesaj gönderilmedi.
