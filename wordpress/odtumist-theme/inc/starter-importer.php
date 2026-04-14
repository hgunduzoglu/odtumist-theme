<?php
if (!defined('ABSPATH')) {
    exit;
}

function odtumist_starter_get_data()
{
    $about_content = <<<'HTML'
<h2 id="neler-yapiyoruz">Neler Yapıyoruz?</h2>
<p>Üyelerimizi, gönüllülerimizi, bursiyerlerimizi ve tüm destekçilerimizi aynı dayanışma ağında buluşturarak İstanbul'daki ODTÜ topluluğunu bir arada tutuyoruz.</p>
<p><strong>"Bütün bu faydayı, Çalışma Gruplarımızın gönüllü katkıları ile devam ettiriyoruz."</strong></p>

<h2 id="calisma-gruplarimiz">Çalışma Gruplarımız</h2>
<p>Edebiyat, felsefe, fotoğraf, sosyal komite, burs, İK &amp; üye geliştirme ve spor &amp; maraton başta olmak üzere farklı alanlarda aktif çalışma gruplarımızla birlikte üretiyoruz.</p>
<p>Çalışma grupları kartları bu sayfanın altında otomatik olarak listelenir.</p>

<h2 id="sen-de-katil">Sen de Katıl Hocam!</h2>
<ul>
<li><a href="https://fonzip.com/odtumist/uyelik" target="_blank" rel="noopener noreferrer">Üye Ol</a></li>
<li><a href="/iletisim/">Gönüllü Ol</a></li>
</ul>

<h2 id="tarihce">Tarihçe</h2>
<p>ODTÜMİST 1986 yılında İstanbul'daki ODTÜ'lülerin emekleriyle şube olarak yolculuğuna başladı ve 2001 yılında bağımsız bir dernek oldu. 40 yıla yaklaşan bu yolculukta burs, mentorluk, mezun buluşmaları ve dayanışma faaliyetleriyle ODTÜ ruhunu İstanbul'da yaşatmaya devam ediyoruz.</p>
<ul>
<li><strong>Efsanevi Et Arabası:</strong> Hurdaya gitmek üzereyken bulunup İstanbul'a getirilen simgesel araç bugün ODTÜPARK'ta korunuyor.</li>
<li><strong>Bilim Ağacı:</strong> Derneğin ısrarlı çabalarıyla daha görünür bir alana taşındı.</li>
<li><strong>Beyaz Masa:</strong> Derneğin öncülük ettiği platform çalışmaları bu yapının doğuşuna katkı sundu.</li>
<li><strong>Bi' Dünya ODTÜ'lü:</strong> Pandemi döneminde küresel dijital mezun buluşmaları organize edildi.</li>
</ul>

<h2 id="yonetim">Yönetim</h2>
<p>Mevcut ve geçmiş dernek yönetimleri, çalışma grupları, tüzük ve faaliyet raporları kurumsal hafızamızın temel parçalarıdır. Bu içerikler WordPress panelinden düzenli olarak güncellenebilir.</p>
HTML;

    $events_content = <<<'HTML'
<h2>Etkinlik Takvimi</h2>
<p>İstanbul ODTÜ Mezunlar Derneği olarak sosyal, kültürel ve sportif etkinliklerde buluşuyoruz. Aşağıda listelenen etkinlik kartları WordPress panelindeki <strong>Etkinlikler</strong> kayıtlarından otomatik üretilir.</p>
<p>Harici kayıt ekranı için: <a href="https://fonzip.com/odtumist/events" target="_blank" rel="noopener noreferrer">fonzip.com/odtumist/events</a></p>
HTML;

    $membership_content = <<<'HTML'
<h2 id="neden-uye-olmaliyim">Neden Üye Olmalıyım?</h2>
<ol>
<li><strong>Dayanışma:</strong> Mezunlar, dernek çatısı altında bir araya gelir ve iletişimde kalır.</li>
<li><strong>Derneğin Sürdürülebilirliği:</strong> Üye, gönüllü, bağışçı ve mentor katkısıyla yapı güçlenir.</li>
<li><strong>Öğrencilere Katkı:</strong> Burs ve mentorluk programlarıyla yeni nesillere destek olunur.</li>
<li><strong>Yeni Mezunların Katılımı:</strong> Zincirin yeni halkaları topluluğa katılır.</li>
<li><strong>Büyüyen Camiamız:</strong> Dayanışma büyüdükçe etki alanı genişler.</li>
</ol>

<h2 id="bilgi-guncelleme">Bilgi Güncelleme</h2>
<p>İletişim ve mezuniyet bilgilerinizi güncel tutmanız, mezun ağının canlı ve güçlü kalmasını sağlar.</p>

<h2 id="aidat-odeme">Aidat Ödeme</h2>
<p>Aidat borcunuzu görüntülemek ve ödeme yapmak için: <a href="https://fonzip.com/odtumist/login" target="_blank" rel="noopener noreferrer">Fonzip Üye Sistemi</a></p>

<h2 id="uyelik-avantajlari">Üyelik Avantajları</h2>
<ul>
<li>Etkinlikler: atölye, seminer, gezi ve spor buluşmaları</li>
<li>Çalışma Grupları: ilgi alanına göre birlikte üretim</li>
<li>Burs Çalışmaları: öğrenciler için dayanışma</li>
<li>Spor &amp; Maraton: iyilik için hareket</li>
<li>Mentorluk: deneyim paylaşımı ve kariyer desteği</li>
<li>ODTÜ Ruhu: networking ve sürekli iletişim</li>
</ul>
HTML;

    $solidarity_content = <<<'HTML'
<h2 id="networking">Networking</h2>
<p>İstanbul'un her köşesinde, her sektöründe bir ODTÜ'lü var. Networking ağımızla mezunlarımızı profesyonel ve sosyal dünyada birbirine bağlıyoruz.</p>

<h2 id="burs">Burs Programları</h2>
<p>1991'den bu yana öğrencilerimizin eğitim yolculuğuna sürekli destek veriyoruz. Burs, mezunun öğrenciye uzattığı güçlü bir dayanışma elidir.</p>

<h2 id="maraton">Maraton &amp; Spor</h2>
<p>İstanbul Maratonu'nda "İyilik Peşinde Koş" yaklaşımıyla burs fonuna destek oluyor, her adımı öğrenciler için umuda dönüştürüyoruz.</p>

<h2 id="mentorluk">Mentorluk</h2>
<p>Deneyimli mezunlarımızı öğrenciler ve genç mezunlarla buluşturarak kariyer yolculuğunu güçlendiren bir paylaşım ortamı kuruyoruz.</p>

<h2 id="bursiyerler">Bursiyer İlişkileri</h2>
<p>Bursiyerlerimizle düzenli buluşmalar, teknik geziler ve sosyal etkinlikler yaparak mezuniyet sonrası hayata hazırlık süreçlerini destekliyoruz.</p>

<h2 id="gonulluler">Gönüllülerimiz</h2>
<p>Etkinliklerden sosyal sorumluluk projelerine kadar derneğin tüm faaliyetleri gönüllülerimizin emeği ve enerjisiyle büyür.</p>

<h2 id="bagiscilar">Bağışçılarımız</h2>
<p>Şeffaf yönetim anlayışımızla her bağışı sosyal etkiye dönüştürüyor, sürdürülebilir katkı mekanizmalarıyla dayanışmayı büyütüyoruz.</p>

<h2 id="paydaslar">Paydaşlarımız</h2>
<p>Üniversitemiz, vakıflar, mezun dernekleri ve kurumsal partnerlerle stratejik iş birlikleri geliştirerek ODTÜ etkisini İstanbul'da güçlendiriyoruz.</p>
HTML;

    $contact_content = <<<'HTML'
<h2>Yönetim Kurulu &amp; Üyelik</h2>
<p><a href="mailto:dernek@odtumist.org">dernek@odtumist.org</a></p>

<h2>Dernek Koordinatörü</h2>
<p><strong>Buket Akpınar</strong><br><a href="mailto:buket.akpinar@odtumist.org">buket.akpinar@odtumist.org</a></p>

<h2>Burs Sorumlusu</h2>
<p><strong>Delal Filizay</strong><br><a href="mailto:delal.filizay@odtumist.org">delal.filizay@odtumist.org</a></p>

<h2>Mesaj Formu</h2>
<p>Bu alana form eklentisi kısa kodu ekleyebilirsin. Örnek:</p>
<p><code>[contact-form-7 id="123" title="İletişim Formu"]</code></p>
HTML;

    return array(
        'theme_mods' => array(
            'odtumist_social_instagram' => 'https://www.instagram.com/odtumist/',
            'odtumist_social_linkedin'  => 'https://www.linkedin.com/in/odtumist/',
            'odtumist_social_x'         => 'https://x.com/odtumist',
            'odtumist_social_facebook'  => 'https://www.facebook.com/groups/23239228710/',
            'odtumist_social_youtube'   => 'https://www.youtube.com/channel/UC0LCfHsf3vCAEBDgMV20YPA',
            'odtumist_cta_membership'   => 'https://fonzip.com/odtumist/uyelik',
            'odtumist_cta_donation'     => 'https://fonzip.com/odtumist/bagis',
            'odtumist_header_donation_label'   => 'BAĞIŞ YAP',
            'odtumist_header_membership_label' => 'ÜYE OL',

            'odtumist_hero_1_image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=2000',
            'odtumist_hero_1_title' => "İSTANBUL'DAKİ ODTÜ'LÜLERİN BULUŞMA NOKTASI",
            'odtumist_hero_1_desc'  => "İstanbul'da yaşayan binlerce ODTÜ'lü olarak iş, bilim, sanat ve girişim dünyalarına uzanan güçlü bir dayanışma ağını hep birlikte yaşatıyoruz.",
            'odtumist_hero_1_primary_label' => 'TANIŞALIM HOCAM!',
            'odtumist_hero_1_primary_url'   => '/hakkimizda/',
            'odtumist_hero_1_secondary_label' => '',
            'odtumist_hero_1_secondary_url'   => '',

            'odtumist_hero_2_image' => 'https://odtumist.org/wp-content/uploads/2021/01/ODTMST-Spr-Maraton-KV1-Banner-02.jpg',
            'odtumist_hero_2_title' => 'BURS VER, YARINLARA NEFES OL',
            'odtumist_hero_2_desc'  => "Burs gönüllüleri arasına katılın, burs verin, maratonda koşun ve ODTÜ öğrencileri için burs toplayın.",
            'odtumist_hero_2_primary_label' => 'BAĞIŞ YAP',
            'odtumist_hero_2_primary_url'   => 'https://fonzip.com/odtumist/bagis',
            'odtumist_hero_2_secondary_label' => 'GÖNÜLLÜ OL',
            'odtumist_hero_2_secondary_url'   => '/iletisim/',

            'odtumist_hero_3_image' => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&q=80&w=2000',
            'odtumist_hero_3_title' => 'MENTOR OL, TECRÜBENİ PAYLAŞ',
            'odtumist_hero_3_desc'  => 'Genç mezunlara ve öğrencilere yol göster, kariyer yolculuklarında onlara ışık tut.',
            'odtumist_hero_3_primary_label' => 'PROGRAMLARI İNCELE',
            'odtumist_hero_3_primary_url'   => '/iletisim/',
            'odtumist_hero_3_secondary_label' => '',
            'odtumist_hero_3_secondary_url'   => '',

            'odtumist_home_events_kicker'      => 'Etkinlik Takvimini Görüntüle',
            'odtumist_home_events_title'       => 'Etkinliklerimiz',
            'odtumist_home_events_description' => "İlgi alanlarınıza göre etkinlikleri filtreleyebilir, İstanbul'daki ODTÜ ruhunu yaşatan buluşmalara katılabilirsiniz.",
            'odtumist_home_membership_title'   => 'Üyelerimizle Varız!',
            'odtumist_home_membership_description' => "Özlediğin ODTÜ ruhunu İstanbul'da yeniden keşfet. Güçlü bir dayanışma ağının parçası ol, öğrencilerin geleceğine dokun.",
            'odtumist_home_membership_button'  => 'ÜYE OL',
            'odtumist_home_volunteer_title'    => 'Gönüllülerimizle Varız!',
            'odtumist_home_volunteer_description' => 'Sosyal ve kültürel etkinliklerimizi düzenle, burs, mentorluk ve maraton gibi çalışmalara destek ver ve kendi projelerini hayata geçir.',
            'odtumist_home_volunteer_button'   => 'GÖNÜLLÜ OL',
            'odtumist_home_groups_kicker'      => 'Birlikte Üretiyoruz',
            'odtumist_home_groups_title'       => 'Çalışma Gruplarımız',
            'odtumist_group_photo'             => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=2000',
            'odtumist_group_photo_title'       => 'DAYANIŞMA GÜCÜMÜZDÜR.',
            'odtumist_group_photo_description' => "ODTÜ'lüler olarak nerede olursak olalım mezuniyetten sonra da ortak değerler etrafında bir araya geliriz. ODTÜMİST, bu ruhu İstanbul'da yaşatır.",

            'odtumist_contact_address' => 'Levazım Mah. Koru Sok. Beşiktaş / İstanbul (ODTÜPARK)',
            'odtumist_contact_phone'   => '+90 (212) 281 40 47',
            'odtumist_contact_email'   => 'dernek@odtumist.org',
            'odtumist_contact_map_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3008.38870198594!2d29.02340337656644!3d41.06047247134375!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab660f7e4f35b%3A0x868b20463c630807!2zT0RUw5xNwLBTVCBWacWfbmVsaWsgVGVzaXNsZXJp!5e0!3m2!1str!2str!4v1700000000000!5m2!1str!2str',
            'odtumist_contact_hero_image' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=2000',
            'odtumist_contact_hero_text'  => "İstanbul'daki ODTÜ ruhunun merkezi ODTÜPARK'ta sizleri bekliyoruz Hocam.",
            'odtumist_footer_description' => "Mezunlarımız arasındaki dayanışmayı artırmak, üniversitemize katkı sağlamak ve toplumsal fayda üretmek amacıyla İstanbul'da faaliyet gösteren köklü bir sivil toplum kuruluşudur.",
        ),
        'pages' => array(
            'anasayfa' => array(
                'title'   => 'Anasayfa',
                'excerpt' => '',
                'content' => '',
                'image'   => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=2000',
            ),
            'hakkimizda' => array(
                'title'   => 'Hakkımızda',
                'excerpt' => "İstanbul'un dinamizminde ODTÜ ruhunu, dayanışmasını ve kültürünü yaşatan topluluğumuza hoş geldin.",
                'content' => $about_content,
                'image'   => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&q=80&w=2000',
            ),
            'etkinlikler' => array(
                'title'   => 'Etkinlikler',
                'excerpt' => "Takvimdeki etkinlikleri inceleyebilir, detay sayfalarından kayıt ve katılım bilgilerine ulaşabilirsin.",
                'content' => $events_content,
                'image'   => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=900',
            ),
            'uyelik' => array(
                'title'   => 'Üyelik',
                'excerpt' => "ODTÜMİST üyeliği; dayanışma, aidiyet ve öğrencilere uzanan etkiyi büyüten güçlü bir topluluk çatısıdır.",
                'content' => $membership_content,
                'image'   => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=800',
            ),
            'dayanisma' => array(
                'title'   => 'Dayanışma',
                'excerpt' => "ODTÜ mezunu olmanın getirdiği bağ, ODTÜMİST çatısı altında ortak bir etki alanına dönüşüyor.",
                'content' => $solidarity_content,
                'image'   => 'https://odtumist.org/wp-content/uploads/2021/01/ODTMST-Spr-Maraton-KV1-Banner-02.jpg',
            ),
            'iletisim' => array(
                'title'   => 'İletişim',
                'excerpt' => "İstanbul'daki ODTÜ ruhunun merkezi ODTÜPARK'ta sizleri bekliyoruz Hocam.",
                'content' => $contact_content,
                'image'   => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=2000',
            ),
            'haberler' => array(
                'title'   => 'Haberler',
                'excerpt' => '',
                'content' => '<p>Güncel duyuru ve haber içeriklerinizi bu bölümden yayınlayabilirsiniz.</p>',
                'image'   => '',
            ),
        ),
        'working_groups' => array(
            array(
                'slug' => 'edebiyat',
                'title' => 'Edebiyat',
                'excerpt' => 'Türk ve Dünya edebiyatının seçkin eserlerini okuyup tartıştığımız, yazarlarla buluştuğumuz ve kendi öykü ve şiirlerimizi de paylaştığımız sıcak ve yaratıcı bir ortam sunuyoruz.',
                'content' => "ODTÜMİST Edebiyat Çalışma Grubu, dünya ve Türk edebiyatının önemli yazarlarını ve eserlerini merkeze alarak okuma, tartışma ve yorumlama kültürünü güçlendirmeyi amaçlayan bir paylaşım topluluğudur. Grup, genç ve güncel yazarları tanımaya, yaşayan yazarlarla doğrudan iletişim kurarak edebiyata dair daha derin bilgiler edinmeye önem verir. Öykü ve şiir geceleri düzenleyerek edebiyatın daha az görünür alanlarını desteklemeyi; üyelerin öykü yazmasını teşvik ederek yaratıcı üretimi artırmayı; mezunlar arasında edebiyat ilgisi üzerinden güçlü bağlar kurmayı hedefler. Grup çalışmalarında seçilen eserler ayrıntılı incelemelerle sunulur; yazarlar, çevirmenler, edebiyat araştırmacıları ve eleştirmenlerle söyleşiler gerçekleştirilir. Üyeler hem Türk hem de dünya edebiyatına dair sunumlar yapar.",
                'image' => 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&q=80&w=800',
            ),
            array(
                'slug' => 'felsefe',
                'title' => 'Felsefe',
                'excerpt' => 'Düşünce sanatına ilgi duyan herkesi, varlık, erdem ve bilgi gibi temel konuları derinlemesine tartışmaya davet ediyoruz.',
                'content' => "ODTÜMİST Felsefe Çalışma Grubu, teknolojinin hızla dönüştürdüğü çağımızda felsefeye artan toplumsal ilgiye yanıt olarak kurulmuştur. Felsefenin bir düşünme sanatı ve özünde bir “bilim sevgisi” olduğu anlayışıyla, dernek çatısı altında nitelikli bir tartışma ve düşünce ortamı oluşturmayı amaçlar. Çalışmalar metin okumaları, tartışmalar ve belirli aralıklarla yapılan felsefi film incelemelerinden oluşur. Grup üyeleri dönemsel toplantılarla üzerinde çalışılacak konuları, okunacak kitapları ve incelenecek filmleri birlikte belirler. Felsefe Çalışma Grubu; varlık, gerçek, erdem, adalet, doğruluk, sanat ve bilgi gibi temel felsefi kavramlara ilgi duyan herkese açıktır.",
                'image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&q=80&w=800',
            ),
            array(
                'slug' => 'fotograf',
                'title' => 'Fotoğraf',
                'excerpt' => 'Fotoğraf üretmeyi, öğrenmeyi ve birlikte yaratmayı seven herkesi Fotoğraf Çalışma Grubu’na katılmaya davet ediyoruz.',
                'content' => "Fotoğraf Çalışma Grubu, ODTÜ mezunlarını ve dostlarını fotoğraf ortak paydasında bir araya getirerek üretmeyi, öğrenmeyi ve paylaşmayı amaçlayan bir topluluktur. Kuruluşundan itibaren eğitimler, geziler, sergiler, söyleşiler ve çeşitli kolektif çalışmalarla gelişen grup; dayanışma, dostluk ve birlikte üretme kültürünü temel değerleri haline getirmiştir. Çalışmalar arasında gösteriler, çevrimiçi haftalık buluşmalar, projeler, dönemsel ve proje kitaplarının hazırlanması ile diğer fotoğraf dernekleriyle ortak üretimler yer alır. Burs yararına takvim üretimi ayrıca yürütülen önemli çalışmalardandır. Fotoğrafa ilgi duyan herkes bu topluluğa katılabilir.",
                'image' => 'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?auto=format&fit=crop&q=80&w=800',
            ),
            array(
                'slug' => 'sosyal-komite',
                'title' => 'Sosyal Komite',
                'excerpt' => 'Gezmek, keşfetmek, paylaşmak ve birlikte eğlenmek istiyorsan ODTÜMİST Sosyal Komite tam sana göre!',
                'content' => "ODTÜMİST Sosyal Komite, ODTÜ mezunlarını farklı temalarda bir araya getiren zengin ve çeşitli etkinliklerle dernek yaşamının sosyal boyutunu güçlendiren aktif bir çalışma grubudur. Komite; mezun buluşmaları, kültür ve tarih turları, şehir içi ve dışı geziler, tadım ve lezzet etkinlikleri, yoga, tango, dil, briç ve satranç gibi hobi kurslarıyla üyelerin bir araya gelmesini sağlar. Geleneksel hale gelen Mezunlar Günü, Bahar Şenliği ve Yılbaşı etkinlikleri, farklı kuşaklardan ODTÜ’lülerin tanışmasına ve bağlarını güçlendirmesine olanak tanır. Tüm etkinlikler komite üyelerinin önerileriyle şekillenir, değerlendirilir ve koordinasyon içinde hayata geçirilir.",
                'image' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&q=80&w=800',
            ),
            array(
                'slug' => 'burs',
                'title' => 'Burs',
                'excerpt' => 'Eğitime destek olmanın ve dayanışmayı büyütmenin bir parçası olmak ister misin? Bursiyerlerin hayatına dokunan projelerde yer alabilirsin.',
                'content' => "İstanbul ODTÜ Mezunları Derneği bünyesinde faaliyet gösteren Burs Çalışma Grubu, ODTÜ’de öğrenim gören ve maddi desteğe ihtiyaç duyan lisans öğrencilerine burs sağlamanın yanı sıra, bursiyerlerin kişisel, sosyal ve toplumsal gelişimlerini desteklemeyi amaçlayan bir dayanışma yapısıdır. Grup, öğrencilerin yalnızca eğitimlerine değil, sorumluluk alan ve topluma katkı sunan bireyler olarak gelişmelerine katkı sağlayan projeleri teşvik eder. Finansal kaynak yaratma, bağış ve maraton çalışmaları, mentorluk destekleri ve yeni projeler grubun temel faaliyetleri arasındadır.",
                'image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=800',
            ),
            array(
                'slug' => 'ik-uye-gelistirme',
                'title' => 'İK & Üye Geliştirme',
                'excerpt' => 'Kariyerini paylaşmak, deneyimini aktarmak ve ODTÜ’lülerin mesleki yolculuğuna katkı sunmak ister misin?',
                'content' => "ODTÜMİST İK & Üye Geliştirme Çalışma Grubu, derneğe üye katılımını artırmayı, mezunlar arasındaki bağı güçlendirmeyi ve ODTÜ mezunları ile öğrencilerin mesleki gelişimlerine katkı sunmayı amaçlar. Grup, mezunların kariyer yolculuklarında karşılaştıkları ihtiyaçları tespit ederek bilgi, deneyim ve uzmanlık paylaşımını destekler; öğrencilerin staj, iş bulma ve girişimcilik süreçlerinde yanlarında olmayı hedefler. Kariyer seminerleri, eğitim etkinlikleri düzenlenir; stajyer–staj yeri eşleşmeleri desteklenir. İK alanında bilgi ve deneyime sahip mezunlar ile uzmanların katkısıyla yürütülen grup, ODTÜMİST topluluğunda kariyer gelişimini güçlendirir.",
                'image' => 'https://images.unsplash.com/photo-1517245385161-12499d63428c?auto=format&fit=crop&q=80&w=800',
            ),
            array(
                'slug' => 'spor-maraton',
                'title' => 'Spor & Maraton',
                'excerpt' => 'Birlikte koşuyor, birlikte yürüyor ve her adımı ODTÜ öğrencileri için umuda çeviriyoruz. Sporla iyiliği buluşturalım!',
                'content' => "Spor & Maraton Çalışma Grubu, ODTÜ mezunlarını ve ODTÜ dostlarını spor, dayanışma ve gönüllülük etrafında bir araya getirerek burs yararına sürdürülebilir farkındalık ve kaynak yaratmayı amaçlar. Özellikle İstanbul Maratonu’nu, yardımlaşma ve birlikte hareket etme kültürünün güçlendiği bir platform olarak ele alır. “Yarınlara Nefes Ol” yaklaşımıyla, atılan her adımı öğrencilere destek olarak geri döndürmeyi hedefler. Grup, yıl boyunca İstanbul Maratonu’na yönelik kampanyalar planlar, ortak koşu etkinlikleri düzenler ve sosyal medya üzerinden bu çalışmaları görünür kılar.",
                'image' => 'https://odtumist.org/wp-content/uploads/2021/01/ODTMST-Spr-Maraton-KV1-Banner-02.jpg',
            ),
        ),
        'events' => array(
            array(
                'slug' => 'geleneksel-visnelik-bulusmasi',
                'title' => 'Geleneksel Vişnelik Buluşması',
                'excerpt' => "Yıllar sonra kampüs havasını İstanbul'da solumak, eski dostlarla kucaklaşmak için beklenen gün geldi.",
                'content' => "Yıllar sonra kampüs havasını İstanbul'da solumak, eski dostlarla kucaklaşmak için beklenen gün geldi.",
                'image' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=800',
                'category' => 'Sosyal',
                'location' => 'ODTÜMİST Vişnelik Tesisleri',
                'start_dt' => '2026-10-15 14:00:00',
            ),
            array(
                'slug' => 'istanbul-maratonu-hazirlik-kosusu',
                'title' => 'İstanbul Maratonu Hazırlık Koşusu',
                'excerpt' => 'Burs fonuna destek için koşuyoruz! Maraton öncesi son hazırlık antrenmanı.',
                'content' => 'Burs fonuna destek için koşuyoruz! Maraton öncesi son hazırlık antrenmanı.',
                'image' => 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?auto=format&fit=crop&q=80&w=800',
                'category' => 'Spor',
                'location' => 'Belgrad Ormanı',
                'start_dt' => '2026-10-29 08:00:00',
            ),
            array(
                'slug' => 'yapay-zeka-ve-sanat-soylesisi',
                'title' => 'Yapay Zeka ve Sanat Söyleşisi',
                'excerpt' => 'Teknolojinin sanat dünyasındaki yansımalarını uzman konuklarımızla tartışıyoruz.',
                'content' => 'Teknolojinin sanat dünyasındaki yansımalarını uzman konuklarımızla tartışıyoruz.',
                'image' => 'https://images.unsplash.com/photo-1547826039-bfc35e0f1ea8?auto=format&fit=crop&q=80&w=800',
                'category' => 'Söyleşi',
                'location' => 'ODTÜMİST Salonu',
                'start_dt' => '2026-10-22 19:30:00',
            ),
            array(
                'slug' => 'siyah-beyaz-istanbul',
                'title' => 'Siyah Beyaz İstanbul',
                'excerpt' => "Fotoğraf kulübümüzle İstanbul sokaklarında nostaljik bir tur.",
                'content' => "Fotoğraf kulübümüzle İstanbul sokaklarında nostaljik bir tur.",
                'image' => 'https://images.unsplash.com/photo-1449034446853-66c86144b0ad?auto=format&fit=crop&q=80&w=800',
                'category' => 'Fotoğraf',
                'location' => 'Karaköy',
                'start_dt' => '2026-11-01 11:00:00',
            ),
        ),
    );
}

function odtumist_starter_prepare_media_import()
{
    if (!function_exists('media_sideload_image')) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
    }
    if (!function_exists('download_url')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    if (!function_exists('wp_generate_attachment_metadata')) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }
}

function odtumist_starter_guess_filename_from_url($url)
{
    $path = (string) wp_parse_url($url, PHP_URL_PATH);
    $name = wp_basename($path);

    if ($name && preg_match('/\.(jpe?g|png|gif|webp)$/i', $name)) {
        return sanitize_file_name($name);
    }

    return 'odtumist-' . md5((string) $url) . '.jpg';
}

function odtumist_starter_import_media($url, $title = '')
{
    $url = esc_url_raw((string) $url);
    if ($url === '') {
        return 0;
    }

    $existing = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'meta_key'       => '_odtumist_source_url',
        'meta_value'     => $url,
        'fields'         => 'ids',
    ));

    if (!empty($existing)) {
        return (int) $existing[0];
    }

    odtumist_starter_prepare_media_import();

    $tmp_file = download_url($url, 60);
    if (is_wp_error($tmp_file)) {
        return $tmp_file;
    }

    $file_array = array(
        'name'     => odtumist_starter_guess_filename_from_url($url),
        'tmp_name' => $tmp_file,
    );

    $attachment_id = media_handle_sideload($file_array, 0, $title);
    if (is_wp_error($attachment_id)) {
        @unlink($tmp_file);
        return $attachment_id;
    }

    update_post_meta((int) $attachment_id, '_odtumist_source_url', $url);
    if ($title !== '') {
        wp_update_post(array(
            'ID'         => (int) $attachment_id,
            'post_title' => sanitize_text_field($title),
        ));
    }

    return (int) $attachment_id;
}

function odtumist_starter_get_post_id_by_slug($post_type, $slug)
{
    $items = get_posts(array(
        'post_type'      => $post_type,
        'name'           => sanitize_title($slug),
        'post_status'    => array('publish', 'draft', 'pending', 'future', 'private', 'trash'),
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ));

    return !empty($items) ? (int) $items[0] : 0;
}

function odtumist_starter_upsert_post($post_type, $slug, $payload)
{
    $existing_id = odtumist_starter_get_post_id_by_slug($post_type, $slug);

    $postarr = array(
        'post_type'    => $post_type,
        'post_status'  => 'publish',
        'post_name'    => sanitize_title($slug),
        'post_title'   => wp_strip_all_tags($payload['title']),
        'post_excerpt' => isset($payload['excerpt']) ? (string) $payload['excerpt'] : '',
        'post_content' => isset($payload['content']) ? (string) $payload['content'] : '',
    );

    if ($existing_id > 0) {
        if ('trash' === get_post_status($existing_id)) {
            wp_untrash_post($existing_id);
        }
        $postarr['ID'] = $existing_id;
        $result = wp_update_post($postarr, true);
    } else {
        $result = wp_insert_post($postarr, true);
    }

    if (is_wp_error($result)) {
        return $result;
    }

    update_post_meta((int) $result, '_odtumist_seed_key', sanitize_title($slug));
    return (int) $result;
}

function odtumist_starter_set_thumbnail($post_id, $image_url, $title, &$report)
{
    if ($image_url === '') {
        return;
    }

    $image_id = odtumist_starter_import_media($image_url, $title);
    if (is_wp_error($image_id)) {
        $report['warnings'][] = 'Görsel indirilemedi: ' . $title . ' (' . $image_url . ') - ' . $image_id->get_error_message();
        return;
    }

    set_post_thumbnail((int) $post_id, (int) $image_id);
}

function odtumist_starter_assign_theme_mods($theme_mods, &$report)
{
    foreach ($theme_mods as $key => $value) {
        set_theme_mod($key, $value);
    }
    $report['messages'][] = 'Theme ayarları JS içerikleriyle güncellendi.';
}

function odtumist_starter_upsert_pages($pages, &$report)
{
    $ids = array();

    foreach ($pages as $slug => $payload) {
        $post_id = odtumist_starter_upsert_post('page', $slug, $payload);
        if (is_wp_error($post_id)) {
            $report['errors'][] = 'Sayfa oluşturulamadı: ' . $payload['title'] . ' (' . $post_id->get_error_message() . ')';
            continue;
        }

        $ids[$slug] = (int) $post_id;
        odtumist_starter_set_thumbnail($post_id, (string) $payload['image'], (string) $payload['title'], $report);
        $report['messages'][] = 'Sayfa güncellendi: ' . $payload['title'];
    }

    return $ids;
}

function odtumist_starter_upsert_working_groups($items, &$report)
{
    foreach ($items as $item) {
        $post_id = odtumist_starter_upsert_post('team', $item['slug'], $item);
        if (is_wp_error($post_id)) {
            $report['errors'][] = 'Çalışma grubu oluşturulamadı: ' . $item['title'] . ' (' . $post_id->get_error_message() . ')';
            continue;
        }

        odtumist_starter_set_thumbnail($post_id, (string) $item['image'], (string) $item['title'], $report);
        $report['messages'][] = 'Çalışma grubu güncellendi: ' . $item['title'];
    }
}

function odtumist_starter_upsert_events($items, &$report)
{
    foreach ($items as $item) {
        $post_id = odtumist_starter_upsert_post('event', $item['slug'], $item);
        if (is_wp_error($post_id)) {
            $report['errors'][] = 'Etkinlik oluşturulamadı: ' . $item['title'] . ' (' . $post_id->get_error_message() . ')';
            continue;
        }

        odtumist_starter_set_thumbnail($post_id, (string) $item['image'], (string) $item['title'], $report);

        if (!empty($item['category'])) {
            wp_set_object_terms($post_id, $item['category'], 'event-category', false);
        }

        if (!empty($item['location'])) {
            update_post_meta($post_id, 'solicitor_event_address', $item['location']);
            update_post_meta($post_id, 'event_address', $item['location']);
        }

        if (!empty($item['start_dt'])) {
            update_post_meta($post_id, 'solicitor_event_start_dt', $item['start_dt']);
        }

        $report['messages'][] = 'Etkinlik güncellendi: ' . $item['title'];
    }
}

function odtumist_starter_get_or_create_menu($menu_name)
{
    $menu_obj = wp_get_nav_menu_object($menu_name);
    if ($menu_obj) {
        return (int) $menu_obj->term_id;
    }

    $created = wp_create_nav_menu($menu_name);
    return is_wp_error($created) ? 0 : (int) $created;
}

function odtumist_starter_reset_menu_items($menu_id)
{
    $items = wp_get_nav_menu_items($menu_id, array('post_status' => 'any'));
    if (empty($items)) {
        return;
    }

    foreach ($items as $item) {
        wp_delete_post((int) $item->ID, true);
    }
}

function odtumist_starter_build_menus($page_ids, &$report)
{
    $primary_menu_id          = odtumist_starter_get_or_create_menu('ODTÜMİST Primary Menu');
    $footer_menu_id           = odtumist_starter_get_or_create_menu('ODTÜMİST Footer Menu');
    $footer_corporate_menu_id = odtumist_starter_get_or_create_menu('ODTÜMİST Footer Corporate Menu');
    $footer_info_menu_id      = odtumist_starter_get_or_create_menu('ODTÜMİST Footer Info Menu');

    if ($primary_menu_id < 1 || $footer_menu_id < 1 || $footer_corporate_menu_id < 1 || $footer_info_menu_id < 1) {
        $report['errors'][] = 'Menü oluşturulamadı.';
        return;
    }

    odtumist_starter_reset_menu_items($primary_menu_id);
    odtumist_starter_reset_menu_items($footer_menu_id);
    odtumist_starter_reset_menu_items($footer_corporate_menu_id);
    odtumist_starter_reset_menu_items($footer_info_menu_id);

    $about_url      = get_permalink($page_ids['hakkimizda']);
    $events_url     = get_permalink($page_ids['etkinlikler']);
    $membership_url = get_permalink($page_ids['uyelik']);
    $solidarity_url = get_permalink($page_ids['dayanisma']);
    $contact_url    = get_permalink($page_ids['iletisim']);

    $primary_items = array(
        array(
            'title'  => 'HAKKIMIZDA',
            'object' => 'page',
            'id'     => $page_ids['hakkimizda'],
            'children' => array(
                array('title' => 'Neler Yapıyoruz?', 'url' => $about_url . '#neler-yapiyoruz'),
                array('title' => 'Çalışma Gruplarımız', 'url' => $about_url . '#calisma-gruplarimiz'),
                array('title' => 'Sen de katıl Hocam!', 'url' => $about_url . '#sen-de-katil'),
                array('title' => 'Tarihçe', 'url' => $about_url . '#tarihce'),
                array('title' => 'Yönetim', 'url' => $about_url . '#yonetim'),
            ),
        ),
        array(
            'title'  => 'ETKİNLİKLER',
            'object' => 'page',
            'id'     => $page_ids['etkinlikler'],
            'children' => array(),
        ),
        array(
            'title'  => 'ÜYELİK',
            'object' => 'page',
            'id'     => $page_ids['uyelik'],
            'children' => array(
                array('title' => 'Neden Üye Olmalıyım?', 'url' => $membership_url . '#neden-uye-olmaliyim'),
                array('title' => 'Bilgi Güncelleme', 'url' => $membership_url . '#bilgi-guncelleme'),
                array('title' => 'Aidat Ödeme', 'url' => $membership_url . '#aidat-odeme'),
                array('title' => 'Üyelik Avantajları', 'url' => $membership_url . '#uyelik-avantajlari'),
            ),
        ),
        array(
            'title'  => 'DAYANIŞMA',
            'object' => 'page',
            'id'     => $page_ids['dayanisma'],
            'children' => array(
                array('title' => 'Networking', 'url' => $solidarity_url . '#networking'),
                array('title' => 'Burs', 'url' => $solidarity_url . '#burs'),
                array('title' => 'Maraton', 'url' => $solidarity_url . '#maraton'),
                array('title' => 'Mentorluk', 'url' => $solidarity_url . '#mentorluk'),
                array('title' => 'Bursiyerler', 'url' => $solidarity_url . '#bursiyerler'),
                array('title' => 'Gönüllüler', 'url' => $solidarity_url . '#gonulluler'),
                array('title' => 'Bağışçılar', 'url' => $solidarity_url . '#bagiscilar'),
                array('title' => 'Paydaşlarımız', 'url' => $solidarity_url . '#paydaslar'),
            ),
        ),
        array(
            'title'  => 'İLETİŞİM',
            'object' => 'page',
            'id'     => $page_ids['iletisim'],
            'children' => array(),
        ),
    );

    foreach ($primary_items as $item) {
        $parent_id = wp_update_nav_menu_item($primary_menu_id, 0, array(
            'menu-item-title'     => $item['title'],
            'menu-item-object'    => 'page',
            'menu-item-object-id' => (int) $item['id'],
            'menu-item-type'      => 'post_type',
            'menu-item-status'    => 'publish',
        ));

        if (is_wp_error($parent_id)) {
            $report['warnings'][] = 'Menü öğesi eklenemedi: ' . $item['title'];
            continue;
        }

        foreach ($item['children'] as $child) {
            wp_update_nav_menu_item($primary_menu_id, 0, array(
                'menu-item-title'     => $child['title'],
                'menu-item-url'       => $child['url'],
                'menu-item-type'      => 'custom',
                'menu-item-parent-id' => (int) $parent_id,
                'menu-item-status'    => 'publish',
            ));
        }
    }

    $footer_items = array(
        array('title' => 'Hakkımızda', 'id' => $page_ids['hakkimizda']),
        array('title' => 'Etkinlikler', 'id' => $page_ids['etkinlikler']),
        array('title' => 'Üyelik', 'id' => $page_ids['uyelik']),
        array('title' => 'Dayanışma', 'id' => $page_ids['dayanisma']),
        array('title' => 'İletişim', 'id' => $page_ids['iletisim']),
    );

    foreach ($footer_items as $item) {
        wp_update_nav_menu_item($footer_menu_id, 0, array(
            'menu-item-title'     => $item['title'],
            'menu-item-object'    => 'page',
            'menu-item-object-id' => (int) $item['id'],
            'menu-item-type'      => 'post_type',
            'menu-item-status'    => 'publish',
        ));
    }

    $corporate_items = array(
        array('title' => 'Bir Bakışta ODTÜMİST', 'url' => $about_url . '#neler-yapiyoruz'),
        array('title' => 'Paydaşlarımız', 'url' => $solidarity_url . '#paydaslar'),
        array('title' => 'Tarihçe', 'url' => $about_url . '#tarihce'),
        array('title' => 'Yönetim', 'url' => $about_url . '#yonetim'),
    );

    foreach ($corporate_items as $item) {
        wp_update_nav_menu_item($footer_corporate_menu_id, 0, array(
            'menu-item-title'  => $item['title'],
            'menu-item-url'    => $item['url'],
            'menu-item-type'   => 'custom',
            'menu-item-status' => 'publish',
        ));
    }

    $reports_url = !empty($page_ids['haberler']) ? get_permalink($page_ids['haberler']) : home_url('/haberler/');
    $info_items = array(
        array('title' => 'Raporlar', 'url' => $reports_url),
        array('title' => 'KVKK', 'url' => $contact_url),
        array('title' => 'Üyelik Şartları', 'url' => $membership_url . '#neden-uye-olmaliyim'),
        array('title' => 'Sıkça Sorulan Sorular', 'url' => $membership_url),
    );

    foreach ($info_items as $item) {
        wp_update_nav_menu_item($footer_info_menu_id, 0, array(
            'menu-item-title'  => $item['title'],
            'menu-item-url'    => $item['url'],
            'menu-item-type'   => 'custom',
            'menu-item-status' => 'publish',
        ));
    }

    $locations = get_theme_mod('nav_menu_locations', array());
    $locations['primary-menu']           = $primary_menu_id;
    $locations['footer-menu']            = $footer_menu_id;
    $locations['footer-corporate-menu']  = $footer_corporate_menu_id;
    $locations['footer-info-menu']       = $footer_info_menu_id;
    set_theme_mod('nav_menu_locations', $locations);

    $report['messages'][] = 'Primary ve Footer menüleri JS yapısına göre oluşturuldu.';
}

function odtumist_starter_cleanup_defaults(&$report)
{
    $default_page = odtumist_starter_get_post_id_by_slug('page', 'sample-page');
    if ($default_page) {
        wp_delete_post($default_page, true);
    }

    $default_post = odtumist_starter_get_post_id_by_slug('post', 'hello-world');
    if ($default_post) {
        wp_delete_post($default_post, true);
    }

    $report['messages'][] = 'Varsayılan örnek içerikler temizlendi.';
}

function odtumist_starter_apply_reading_settings($page_ids, &$report)
{
    if (!empty($page_ids['anasayfa'])) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', (int) $page_ids['anasayfa']);
    }

    if (!empty($page_ids['haberler'])) {
        update_option('page_for_posts', (int) $page_ids['haberler']);
    }

    flush_rewrite_rules(false);
    $report['messages'][] = 'Okuma ayarları güncellendi (statik anasayfa + haberler).';
}

function odtumist_run_starter_import()
{
    @set_time_limit(300);

    $report = array(
        'messages' => array(),
        'warnings' => array(),
        'errors'   => array(),
    );

    $data = odtumist_starter_get_data();

    odtumist_starter_assign_theme_mods($data['theme_mods'], $report);
    $page_ids = odtumist_starter_upsert_pages($data['pages'], $report);
    odtumist_starter_upsert_working_groups($data['working_groups'], $report);
    odtumist_starter_upsert_events($data['events'], $report);

    if (!empty($page_ids['hakkimizda']) && !empty($page_ids['etkinlikler']) && !empty($page_ids['uyelik']) && !empty($page_ids['dayanisma']) && !empty($page_ids['iletisim'])) {
        odtumist_starter_build_menus($page_ids, $report);
        odtumist_starter_apply_reading_settings($page_ids, $report);
    } else {
        $report['errors'][] = 'Menü ve okuma ayarları için gerekli sayfalar eksik.';
    }

    odtumist_starter_cleanup_defaults($report);

    update_option('odtumist_starter_imported_at', current_time('mysql'));
    return $report;
}

function odtumist_starter_admin_menu()
{
    add_theme_page(
        __('ODTÜMİST Starter Import', 'odtumist'),
        __('ODTÜMİST Starter Import', 'odtumist'),
        'manage_options',
        'odtumist-starter-import',
        'odtumist_starter_render_admin_page'
    );
}
add_action('admin_menu', 'odtumist_starter_admin_menu');

function odtumist_starter_render_admin_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Bu alana erişim yetkiniz yok.', 'odtumist'));
    }

    $report = null;
    if (isset($_POST['odtumist_run_starter_import'])) {
        check_admin_referer('odtumist_starter_import_nonce');
        $report = odtumist_run_starter_import();
    }

    $last_run = get_option('odtumist_starter_imported_at');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('ODTÜMİST Starter Import', 'odtumist'); ?></h1>
        <p><?php esc_html_e('Bu araç JS tasarımındaki içerikleri WordPress native yapıya aktarır: sayfalar, menüler, etkinlikler, çalışma grupları, tema ayarları ve görseller.', 'odtumist'); ?></p>
        <?php if (!empty($last_run)) : ?>
            <p><strong><?php esc_html_e('Son çalıştırma:', 'odtumist'); ?></strong> <?php echo esc_html($last_run); ?></p>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('odtumist_starter_import_nonce'); ?>
            <p>
                <button type="submit" name="odtumist_run_starter_import" value="1" class="button button-primary button-hero">
                    <?php esc_html_e('Starter İçeriği Kur / Güncelle', 'odtumist'); ?>
                </button>
            </p>
        </form>

        <?php if (is_array($report)) : ?>
            <hr>
            <h2><?php esc_html_e('İşlem Sonucu', 'odtumist'); ?></h2>

            <?php if (!empty($report['errors'])) : ?>
                <div class="notice notice-error">
                    <p><strong><?php esc_html_e('Hatalar:', 'odtumist'); ?></strong></p>
                    <ul>
                        <?php foreach ($report['errors'] as $error) : ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($report['warnings'])) : ?>
                <div class="notice notice-warning">
                    <p><strong><?php esc_html_e('Uyarılar:', 'odtumist'); ?></strong></p>
                    <ul>
                        <?php foreach ($report['warnings'] as $warning) : ?>
                            <li><?php echo esc_html($warning); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($report['messages'])) : ?>
                <div class="notice notice-success">
                    <p><strong><?php esc_html_e('Tamamlanan Adımlar:', 'odtumist'); ?></strong></p>
                    <ul>
                        <?php foreach ($report['messages'] as $message) : ?>
                            <li><?php echo esc_html($message); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}
