
import { Event, NewsItem, WorkingGroup } from './types';

export const ODTU_RED = '#E31E24';

export const SOCIAL_LINKS = {
  instagram: 'https://www.instagram.com/odtumist/',
  linkedin: 'https://www.linkedin.com/in/odtumist/',
  x: 'https://x.com/odtumist',
  facebook: 'https://www.facebook.com/groups/23239228710/',
  youtube: 'https://www.youtube.com/channel/UC0LCfHsf3vCAEBDgMV20YPA'
};

export const NAV_ITEMS = [
  { 
    label: 'HAKKIMIZDA', 
    subItems: [
      { label: 'Neler Yapıyoruz?', view: 'ABOUT_DOING' },
      { label: 'Çalışma Gruplarımız', view: 'ABOUT_GROUPS' },
      { label: 'Sen de katıl Hocam!', view: 'ABOUT_JOIN' },
      { label: 'Tarihçe', view: 'ABOUT_HISTORY' },
      { label: 'Yönetim', view: 'ABOUT_MANAGEMENT' }
    ] 
  },
  { label: 'ETKİNLİKLER', subItems: [] },
  { 
    label: 'ÜYELİK', 
    subItems: [
      { label: 'Neden Üye Olmalıyım?', view: 'MEMBERSHIP_WHY' },
      { label: 'Bilgi Güncelleme', view: 'MEMBERSHIP_UPDATE' },
      { label: 'Aidat Ödeme', view: 'MEMBERSHIP_DUES' },
      { label: 'Üyelik Avantajları', view: 'MEMBERSHIP_BENEFITS' }
    ] 
  },
  { 
    label: 'DAYANIŞMA', 
    subItems: [
      { label: 'Networking', view: 'SOLIDARITY_NETWORKING' },
      { label: 'Burs', view: 'SOLIDARITY_BURS' },
      { label: 'Maraton', view: 'SOLIDARITY_MARATON' },
      { label: 'Mentorluk', view: 'SOLIDARITY_MENTORLUK' },
      { label: 'Bursiyerler', view: 'SOLIDARITY_BURSIYERLER' },
      { label: 'Gönüllüler', view: 'SOLIDARITY_GONULLULER' },
      { label: 'Bağışçılar', view: 'SOLIDARITY_BAGISCILAR' },
      { label: 'Paydaşlarımız', view: 'SOLIDARITY_PAYDASLAR' }
    ] 
  },
  { label: 'İLETİŞİM', subItems: [] },
];

export const WORKING_GROUPS: WorkingGroup[] = [
  { 
    id: '1', 
    title: 'Edebiyat', 
    description: 'Türk ve Dünya edebiyatının seçkin eserlerini okuyup tartıştığımız, yazarlarla buluştuğumuz ve kendi öykü ve şiirlerimizi de paylaştığımız sıcak ve yaratıcı bir ortam sunuyoruz.', 
    longDescription: 'ODTÜMİST Edebiyat Çalışma Grubu, dünya ve Türk edebiyatının önemli yazarlarını ve eserlerini merkeze alarak okuma, tartışma ve yorumlama kültürünü güçlendirmeyi amaçlayan bir paylaşım topluluğudur. Grup, genç ve güncel yazarları tanımaya, yaşayan yazarlarla doğrudan iletişim kurarak edebiyata dair daha derin bilgiler edinmeye önem verir. Öykü ve şiir geceleri düzenleyerek edebiyatın daha az görünür alanlarını desteklemeyi; üyelerin öykü yazmasını teşvik ederek yaratıcı üretimi artırmayı; mezunlar arasında edebiyat ilgisi üzerinden güçlü bağlar kurmayı hedefler. Grup çalışmalarında seçilen eserler ayrıntılı incelemelerle sunulur; yazarlar, çevirmenler, edebiyat araştırmacıları ve eleştirmenlerle söyleşiler gerçekleştirilir. Üyeler hem Türk hem de dünya edebiyatına dair sunumlar yapar.',
    image: 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&q=80&w=800',
    color: 'bg-amber-600'
  },
  { 
    id: '2', 
    title: 'Felsefe', 
    description: 'Düşünce sanatına ilgi duyan herkesi, varlık, erdem ve bilgi gibi temel konuları derinlemesine tartışmaya davet ediyoruz.', 
    longDescription: 'ODTÜMİST Felsefe Çalışma Grubu, teknolojinin hızla dönüştürdüğü çağımızda felsefeye artan toplumsal ilgiye yanıt olarak kurulmuştur. Felsefenin bir düşünme sanatı ve özünde bir “bilim sevgisi” olduğu anlayışıyla, dernek çatısı altında nitelikli bir tartışma ve düşünce ortamı oluşturmayı amaçlar. Çalışmalar metin okumaları, tartışmalar ve belirli aralıklarla yapılan felsefi film incelemelerinden oluşur. Grup üyeleri dönemsel toplantılarla üzerinde çalışılacak konuları, okunacak kitapları ve incelenecek filmleri birlikte belirler. Felsefe Çalışma Grubu; varlık, gerçek, erdem, adalet, doğruluk, sanat ve bilgi gibi temel felsefi kavramlara ilgi duyan herkese açıktır.',
    image: 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&q=80&w=800',
    color: 'bg-yellow-700'
  },
  { 
    id: '3', 
    title: 'Fotoğraf', 
    description: 'Fotoğraf üretmeyi, öğrenmeyi ve birlikte yaratmayı seven herkesi Fotoğraf Çalışma Grubu’na katılmaya davet ediyoruz.', 
    longDescription: 'Fotoğraf Çalışma Grubu, ODTÜ mezunlarını ve dostlarını fotoğraf ortak paydasında bir araya getirerek üretmeyi, öğrenmeyi ve paylaşmayı amaçlayan bir topluluktur. Kuruluşundan itibaren eğitimler, geziler, sergiler, söyleşiler ve çeşitli kolektif çalışmalarla gelişen grup; dayanışma, dostluk ve birlikte üretme kültürünü temel değerleri haline getirmiştir. Çalışmalar arasında gösteriler, çevrimiçi haftalık buluşmalar, projeler, dönemsel ve proje kitaplarının hazırlanması ile diğer fotoğraf dernekleriyle ortak üretimler yer alır. Burs yararına takvim üretimi ayrıca yürütülen önemli çalışmalardandır. Fotoğrafa ilgi duyan herkes bu topluluğa katılabilir.',
    image: 'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?auto=format&fit=crop&q=80&w=800',
    color: 'bg-blue-600'
  },
  { 
    id: '4', 
    title: 'Sosyal Komite', 
    description: 'Gezmek, keşfetmek, paylaşmak ve birlikte eğlenmek istiyorsan ODTÜMİST Sosyal Komite tam sana göre!', 
    longDescription: 'ODTÜMİST Sosyal Komite, ODTÜ mezunlarını farklı temalarda bir araya getiren zengin ve çeşitli etkinliklerle dernek yaşamının sosyal boyutunu güçlendiren aktif bir çalışma grubudur. Komite; mezun buluşmaları, kültür ve tarih turları, şehir içi ve dışı geziler, tadım ve lezzet etkinlikleri, yoga, tango, dil, briç ve satranç gibi hobi kurslarıyla üyelerin bir araya gelmesini sağlar. Geleneksel hale gelen Mezunlar Günü, Bahar Şenliği ve Yılbaşı etkinlikleri, farklı kuşaklardan ODTÜ’lülerin tanışmasına ve bağlarını güçlendirmesine olanak tanır. Tüm etkinlikler komite üyelerinin önerileriyle şekillenir, değerlendirilir ve koordinasyon içinde hayata geçirilir.',
    image: 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&q=80&w=800',
    color: 'bg-orange-500'
  },
  { 
    id: '5', 
    title: 'Burs', 
    description: 'Eğitime destek olmanın ve dayanışmayı büyütmenin bir parçası olmak ister misin? Bursiyerlerin hayatına dokunan projelerde yer alabilirsin.', 
    longDescription: 'İstanbul ODTÜ Mezunları Derneği bünyesinde faaliyet gösteren Burs Çalışma Grubu, ODTÜ’de öğrenim gören ve maddi desteğe ihtiyaç duyan lisans öğrencilerine burs sağlamanın yanı sıra, bursiyerlerin kişisel, sosyal ve toplumsal gelişimlerini desteklemeyi amaçlayan bir dayanışma yapısıdır. Grup, öğrencilerin yalnızca eğitimlerine değil, sorumluluk alan ve topluma katkı sunan bireyler olarak gelişmelerine katkı sağlayan projeleri teşvik eder. Finansal kaynak yaratma, bağış ve maraton çalışmaları, mentorluk destekleri ve yeni projeler grubun temel faaliyetleri arasındadır.',
    image: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=800',
    color: 'bg-red-600'
  },
  { 
    id: '6', 
    title: 'İK & Üye Geliştirme', 
    description: 'Kariyerini paylaşmak, deneyimini aktarmak ve ODTÜ’lülerin mesleki yolculuğuna katkı sunmak ister misin?', 
    longDescription: 'ODTÜMİST İK & Üye Geliştirme Çalışma Grubu, derneğe üye katılımını artırmayı, mezunlar arasındaki bağı güçlendirmeyi ve ODTÜ mezunları ile öğrencilerin mesleki gelişimlerine katkı sunmayı amaçlar. Grup, mezunların kariyer yolculuklarında karşılaştıkları ihtiyaçları tespit ederek bilgi, deneyim ve uzmanlık paylaşımını destekler; öğrencilerin staj, iş bulma ve girişimcilik süreçlerinde yanlarında olmayı hedefler. Kariyer seminerleri, eğitim etkinlikleri düzenlenir; stajyer–staj yeri eşleşmeleri desteklenir. İK alanında bilgi ve deneyime sahip mezunlar ile uzmanların katkısıyla yürütülen grup, ODTÜMİST topluluğunda kariyer gelişimini güçlendirir.',
    image: 'https://images.unsplash.com/photo-1517245385161-12499d63428c?auto=format&fit=crop&q=80&w=800',
    color: 'bg-orange-600'
  },
  { 
    id: '7', 
    title: 'Spor & Maraton', 
    description: 'Birlikte koşuyor, birlikte yürüyor ve her adımı ODTÜ öğrencileri için umuda çeviriyoruz. Sporla iyiliği buluşturalım!', 
    longDescription: 'Spor & Maraton Çalışma Grubu, ODTÜ mezunlarını ve ODTÜ dostlarını spor, dayanışma ve gönüllülük etrafında bir araya getirerek burs yararına sürdürülebilir farkındalık ve kaynak yaratmayı amaçlar. Özellikle İstanbul Maratonu’nu, yardımlaşma ve birlikte hareket etme kültürünün güçlendiği bir platform olarak ele alır. “Yarınlara Nefes Ol” yaklaşımıyla, atılan her adımı öğrencilere destek olarak geri döndürmeyi hedefler. Grup, yıl boyunca İstanbul Maratonu’na yönelik kampanyalar planlar, ortak koşu etkinlikleri düzenler ve sosyal medya üzerinden bu çalışmaları görünür kılar.',
    image: 'https://odtumist.org/wp-content/uploads/2021/01/ODTMST-Spr-Maraton-KV1-Banner-02.jpg',
    color: 'bg-purple-600'
  }
];

export const FEATURED_EVENTS: Event[] = [
  {
    id: '1',
    title: 'Geleneksel Vişnelik Buluşması',
    date: '15 Ekim 2023 - 14:00',
    location: 'ODTÜMİST Vişnelik Tesisleri',
    category: 'Sosyal',
    image: 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=800',
    description: 'Yıllar sonra kampüs havasını İstanbul\'da solumak, eski dostlarla kucaklaşmak için beklenen gün geldi.'
  },
  {
    id: '2',
    title: 'İstanbul Maratonu Hazırlık Koşusu',
    date: '29 Ekim 2023 - 08:00',
    location: 'Belgrad Ormanı',
    category: 'Spor',
    image: 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?auto=format&fit=crop&q=80&w=800',
    description: 'Burs fonuna destek için koşuyoruz! Maraton öncesi son hazırlık antrenmanı.'
  },
  {
    id: '3',
    title: 'Yapay Zeka ve Sanat Söyleşisi',
    date: '22 Ekim 2023 - 19:30',
    location: 'ODTÜMİST Salonu',
    category: 'Söyleşi',
    image: 'https://images.unsplash.com/photo-1547826039-bfc35e0f1ea8?auto=format&fit=crop&q=80&w=800',
    description: 'Teknolojinin sanat dünyasındaki yansımalarını uzman konuklarımızla tartışıyoruz.'
  },
  {
    id: '4',
    title: 'Siyah Beyaz İstanbul',
    date: '01 Kasım 2023 - 11:00',
    location: 'Karaköy',
    category: 'Fotoğraf',
    image: 'https://images.unsplash.com/photo-1449034446853-66c86144b0ad?auto=format&fit=crop&q=80&w=800',
    description: 'Fotoğraf kulübümüzle İstanbul sokaklarında nostaljik bir tur.'
  }
];

export const SYSTEM_INSTRUCTION = `
Sen ODTÜMİST'in (İstanbul ODTÜ Mezunlar Derneği) yardımsever, enerjik ve bilgili yapay zeka asistanısın.
Adın "VişneBot".
Görevin:
1. Mezunlara yaklaşan etkinlikler hakkında bilgi vermek.
2. ODTÜ Burs Fonu hakkında bilgi verip bağış yapmaya teşvik etmek.
3. Üyelik süreçleri hakkında rehberlik etmek.
4. "Hocam" hitabını ara sıra samimiyet kurmak için kullanabilirsin ama profesyonelliği koru.
5. Sorulara kısa, net ve Türkçe cevap ver.
6. ODTÜ ruhunu yansıt: Bilimsel, çağdaş ve toplumsal duyarlılığı yüksek.
`;
