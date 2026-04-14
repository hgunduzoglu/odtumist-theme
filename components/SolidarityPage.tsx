
import React, { useEffect } from 'react';
import { 
  Globe, 
  GraduationCap, 
  Trophy, 
  Coffee, 
  Users, 
  Heart, 
  Handshake, 
  Building2, 
  ArrowRight,
  Sparkles
} from 'lucide-react';

interface SolidarityPageProps {
  initialSection?: string;
}

const SolidarityPage: React.FC<SolidarityPageProps> = ({ initialSection }) => {
  useEffect(() => {
    if (initialSection) {
      const element = document.getElementById(initialSection);
      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  }, [initialSection]);

  const SECTIONS = [
    {
      id: 'networking',
      title: 'Networking',
      desc: 'İstanbul\'un her köşesinde, her sektöründe bir ODTÜ\'lü var. Networking ağımızla mezunlarımızı profesyonel ve sosyal dünyada birbirine bağlıyoruz. Kariyer fırsatları, sektörel iş birlikleri ve tecrübe paylaşımı için en güçlü bağınız ODTÜ ruhu.',
      btnLabel: 'Ağa Katıl',
      icon: <Globe size={60} />,
      bg: 'bg-odtu-blue',
      textColor: 'text-white'
    },
    {
      id: 'burs',
      title: 'Burs Programları',
      desc: 'Öğrencilerimizin eğitim hayatlarını kesintisiz sürdürebilmeleri için 1991\'den beri her yıl binlerce öğrenciye destek oluyoruz. Sadece maddi bir katkı değil, bir mezunun öğrencisine el uzatmasıdır burs. Geleceğin ODTÜ\'lülerini birlikte yetiştiriyoruz.',
      btnLabel: 'Keşfet',
      icon: <GraduationCap size={60} />,
      bg: 'bg-slate-50',
      textColor: 'text-slate-900',
      accentColor: 'text-odtu-red'
    },
    {
      id: 'maraton',
      title: 'Maraton & Spor',
      desc: 'Her yıl İstanbul Maratonu\'nda binlerce mezunumuzla öğrencilerimizin burs fonu için koşuyoruz. "İyilik Peşinde Koş" mottosuyla ter döküyor, her adımda bir öğrencinin eğitimine nefes oluyoruz. Sporun birleştirici gücüyle dayanışmamızı sokaklara taşıyoruz.',
      btnLabel: 'Destekle',
      icon: <Trophy size={60} />,
      bg: 'bg-orange-500',
      textColor: 'text-white'
    },
    {
      id: 'mentorluk',
      title: 'Mentorluk',
      desc: 'Tecrübe, paylaşıldıkça değer kazanır. Deneyimli mezunlarımızı kariyer yolculuğunun başındaki genç mezunlarımız ve öğrencilerimizle eşleştiriyoruz. Sektörel rehberlikten kişisel gelişime uzanan bu yolculukta ODTÜ geleneğini sürdürüyoruz.',
      btnLabel: 'Katıl',
      icon: <Coffee size={60} />,
      bg: 'bg-odtu-red',
      textColor: 'text-white'
    },
    {
      id: 'bursiyerler',
      title: 'Bursiyer İlişkileri',
      desc: 'Bursiyerlerimiz bizim sadece desteklediğimiz öğrenciler değil, ailemizin en genç üyeleridir. Onlarla düzenli buluşmalar, teknik geziler ve sosyal etkinlikler düzenleyerek mezuniyet sonrası hayata hazırlıyor, ODTÜ ruhunu İstanbul\'da aşılamaya devam ediyoruz.',
      btnLabel: 'Mezun-Öğrenci Dayanışması',
      icon: <Users size={60} />,
      bg: 'bg-blue-50',
      textColor: 'text-slate-900',
      accentColor: 'text-odtu-blue'
    },
    {
      id: 'gonulluler',
      title: 'Gönüllülerimiz',
      desc: 'Derneğimizin kalbi gönüllülerimizdir. Etkinlik planlamadan sosyal sorumluluk projelerine, dijital iletişimden saha çalışmalarına kadar her alanda gönüllü ODTÜ\'lülerin enerjisiyle büyüyoruz. Siz de zamanınızı ve yeteneğinizi bu topluluk için paylaşın.',
      btnLabel: 'Harekete Geç',
      icon: <Heart size={60} />,
      bg: 'bg-slate-900',
      textColor: 'text-white'
    },
    {
      id: 'bagiscilar',
      title: 'Bağışçılarımız',
      desc: 'Şeffaf ve hesap verebilir yönetim anlayışımızla, her kuruş bağışın doğrudan amacına ulaşmasını sağlıyoruz. Kurumsal veya bireysel bağışlarınızla ODTÜMİST\'in sürdürülebilirliğine ve yarattığı sosyal etkiye ortak olun. İyiliği birlikte büyütelim.',
      btnLabel: 'İncele',
      icon: <Handshake size={60} />,
      bg: 'bg-amber-50',
      textColor: 'text-slate-900',
      accentColor: 'text-amber-600'
    },
    {
      id: 'paydaslar',
      title: 'Paydaşlarımız',
      desc: 'Üniversitemiz, diğer mezun dernekleri, vakıflar ve kurumsal partnerlerimizle ortak hedefler doğrultusunda çalışıyoruz. Paydaşlarımızla kurduğumuz stratejik iş birlikleriyle İstanbul\'daki ODTÜ etkisini ve görünürlüğünü en üst seviyeye taşıyoruz.',
      btnLabel: 'Keşfet',
      icon: <Building2 size={60} />,
      bg: 'bg-white',
      textColor: 'text-slate-900',
      bordered: true
    }
  ];

  return (
    <div className="pt-20 bg-white min-h-screen">
      {/* Header */}
      <section className="relative py-24 bg-slate-900 overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          <div className="absolute top-0 right-0 w-96 h-96 bg-odtu-red rounded-full blur-[100px]" />
          <div className="absolute bottom-0 left-0 w-96 h-96 bg-odtu-blue rounded-full blur-[100px]" />
        </div>
        <div className="relative max-w-7xl mx-auto px-4 text-center">
          <div className="inline-flex items-center gap-2 px-4 py-2 bg-white/10 text-white rounded-full text-xs font-black uppercase tracking-[0.2em] mb-6">
            <Sparkles size={16} />
            Dayanışma Ekosistemi
          </div>
          <h1 className="text-5xl md:text-8xl font-black text-white uppercase tracking-tighter mb-6">
            BİRLİKTE <span className="text-odtu-red">DAHA GÜÇLÜYÜZ</span>
          </h1>
          <p className="text-xl text-slate-400 max-w-3xl mx-auto font-medium">
            ODTÜ mezunu olmanın getirdiği o sarsılmaz bağ, İstanbul'da ODTÜMİST çatısı altında bir yaşam biçimine dönüşüyor.
          </p>
        </div>
      </section>

      {/* Grid Sections */}
      <section className="flex flex-col">
        {SECTIONS.map((section, idx) => (
          <div 
            key={section.id} 
            id={section.id}
            className={`w-full py-24 md:py-32 flex flex-col items-center justify-center transition-all ${section.bg} ${section.bordered ? 'border-t border-slate-100' : ''}`}
          >
            <div className="max-w-6xl mx-auto px-4 w-full flex flex-col md:flex-row items-center gap-16">
              <div className={`w-32 h-32 md:w-48 md:h-48 rounded-[2.5rem] flex items-center justify-center shadow-2xl shrink-0 ${section.textColor === 'text-white' ? 'bg-white/10 backdrop-blur' : 'bg-white shadow-slate-200'}`}>
                <div className={section.accentColor || section.textColor}>
                  {section.icon}
                </div>
              </div>
              
              <div className="flex-1 space-y-8 text-center md:text-left">
                <h2 className={`text-4xl md:text-6xl font-black uppercase tracking-tighter ${section.textColor}`}>
                  {section.title}
                </h2>
                <p className={`text-lg md:text-2xl font-medium leading-relaxed opacity-90 ${section.textColor}`}>
                  {section.desc}
                </p>
                <div className="flex justify-center md:justify-start">
                  <button className={`px-10 py-5 rounded-full font-black text-lg flex items-center gap-3 transition-all hover:scale-105 active:scale-95 shadow-xl ${
                    section.textColor === 'text-white' 
                    ? 'bg-white text-slate-900 hover:bg-slate-100' 
                    : 'bg-slate-900 text-white hover:bg-odtu-red'
                  }`}>
                    {section.btnLabel.toUpperCase()}
                    <ArrowRight size={24} />
                  </button>
                </div>
              </div>
            </div>
          </div>
        ))}
      </section>

      {/* Final Call to Action */}
      <section className="bg-odtu-red py-24 text-center text-white">
        <div className="max-w-4xl mx-auto px-4">
          <h2 className="text-4xl md:text-7xl font-black uppercase mb-8 tracking-tighter">ODTÜ RUHUNU ŞİMDİ YAŞATIN</h2>
          <p className="text-xl md:text-2xl font-bold mb-12 opacity-90">Siz hangi alanda dayanışmaya katılmak istersiniz Hocam?</p>
          <button className="bg-white text-odtu-red px-16 py-6 rounded-full font-black text-2xl hover:bg-slate-900 hover:text-white transition-all shadow-2xl">
            İLETİŞİME GEÇİN
          </button>
        </div>
      </section>
    </div>
  );
};

export default SolidarityPage;
