
import React, { useState, useEffect } from 'react';
import { Users, History, Info, Sparkles, ArrowRight, ArrowLeft, Heart, Send, ExternalLink, MessageSquare, Target, Calendar, Users2, X, HelpCircle, FileText, ShieldCheck, Briefcase } from 'lucide-react';
import { ViewState, WorkingGroup } from '../types';
import { WORKING_GROUPS } from '../constants';

interface AboutPageProps {
  onNavigate: (view: ViewState, params?: any) => void;
  initialTab?: string;
}

const TABS = [
  { id: 'doing', label: 'Neler Yapıyoruz?', icon: <Sparkles size={18} /> },
  { id: 'groups', label: 'Çalışma Gruplarımız', icon: <Target size={18} /> },
  { id: 'join', label: 'Sen de katıl Hocam!', icon: <Users size={18} /> },
  { id: 'history', label: 'Tarihçe', icon: <History size={18} /> },
  { id: 'management', label: 'Yönetim', icon: <Info size={18} /> }
];

const HISTORY_FACTS = [
  {
    title: 'Efsanevi "Et Arabası"',
    desc: '1970 öncesi kampüste servis aracı olarak kullanılan ve öğrencilerin "Et Arabası" dediği o meşhur kırmızı otobüslerden sonuncusu, hurdaya gitmek üzereyken Mersin’de bulunup tırlarla İstanbul’a getirilmiş! Şu an Ulus’taki tesislerde korunuyor.',
    image: 'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&q=80&w=800',
    color: 'border-odtu-red'
  },
  {
    title: 'Bilim Ağacı’nın Taşınma Hikayesi',
    desc: 'Hazırlık okulunun oradaki Bilim Ağacı heykelinin, ağaçlar arasında kaybolup görünmez hale gelmesi üzerine derneğin ısrarlı çabalarıyla 1991 yılında şu anki merkezi yerine taşındığını biliyor muydun?',
    image: 'https://images.unsplash.com/photo-1549490349-8643362247b5?auto=format&fit=crop&q=80&w=800',
    color: 'border-odtu-blue'
  },
  {
    title: 'Beyaz Masa’nın Doğuşu',
    desc: '1994-95 yıllarında derneğin öncülük ettiği çevre platformunun çalışmalarının, bugün İstanbul Büyükşehir Belediyesi’nde hepimizin bildiği "Beyaz Masa" uygulamasının kurulmasına vesile olduğunu biliyor muydun?',
    image: 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&q=80&w=800',
    color: 'border-slate-400'
  },
  {
    title: '"Bi\' Dünya ODTÜ\'lü"',
    desc: 'Pandemi döneminde fiziksel buluşmalar iptal olunca, derneğin dünyadaki tüm ODTÜ\'lüleri dijitalde bir araya getirerek 28 farklı oturumla devasa bir global buluşma organize ettiğini biliyor muydun?',
    image: 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&q=80&w=800',
    color: 'border-orange-500'
  }
];

const AboutPage: React.FC<AboutPageProps> = ({ onNavigate, initialTab }) => {
  const [activeTab, setActiveTab] = useState(initialTab || 'doing');
  const [selectedGroup, setSelectedGroup] = useState<WorkingGroup | null>(null);

  useEffect(() => {
    if (initialTab) setActiveTab(initialTab);
  }, [initialTab]);

  const currentIdx = TABS.findIndex(t => t.id === activeTab);
  const prevTab = currentIdx > 0 ? TABS[currentIdx - 1] : null;
  const nextTab = currentIdx < TABS.length - 1 ? TABS[currentIdx + 1] : null;

  const handleTabChange = (id: string) => {
    setActiveTab(id);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const PaginationButtons = () => (
    <div className="flex justify-between items-center mt-24 pt-10 border-t border-slate-100">
      {prevTab ? (
        <button 
          onClick={() => handleTabChange(prevTab.id)}
          className="flex items-center gap-3 px-8 py-4 rounded-full border-2 border-slate-200 text-slate-500 font-black hover:border-odtu-red hover:text-odtu-red transition-all group uppercase text-sm tracking-widest"
        >
          <ArrowLeft size={20} className="group-hover:-translate-x-1 transition-transform" />
          ÖNCEKİ: {prevTab.label}
        </button>
      ) : <div />}
      
      {nextTab ? (
        <button 
          onClick={() => handleTabChange(nextTab.id)}
          className="flex items-center gap-3 px-10 py-5 rounded-full bg-slate-900 text-white font-black hover:bg-odtu-red transition-all group shadow-2xl uppercase text-sm tracking-widest"
        >
          SONRAKİ: {nextTab.label}
          <ArrowRight size={20} className="group-hover:translate-x-1 transition-transform" />
        </button>
      ) : <div />}
    </div>
  );

  return (
    <div className="pt-20 bg-white min-h-screen flex flex-col">
      {/* Detail Modal */}
      {selectedGroup && (
        <div className="fixed inset-0 z-[60] flex items-center justify-center p-4 md:p-10 animate-fade-in">
          <div className="absolute inset-0 bg-slate-900/90 backdrop-blur-xl" onClick={() => setSelectedGroup(null)} />
          <div className="relative bg-white w-full max-w-6xl max-h-[90vh] overflow-hidden rounded-[3rem] shadow-2xl flex flex-col md:flex-row">
            <button 
              onClick={() => setSelectedGroup(null)}
              className="absolute top-6 right-6 z-10 p-4 bg-black/10 hover:bg-black/20 rounded-full transition-all"
            >
              <X size={24} />
            </button>
            
            <div className="w-full md:w-1/2 h-64 md:h-auto overflow-hidden relative">
              <img src={selectedGroup.image} alt={selectedGroup.title} className="w-full h-full object-cover" />
              <div className={`absolute inset-0 ${selectedGroup.color || 'bg-odtu-red'} opacity-30 mix-blend-multiply`} />
              <div className="absolute bottom-10 left-10 text-white">
                <h2 className="text-4xl md:text-6xl font-black uppercase tracking-tighter drop-shadow-lg">{selectedGroup.title}</h2>
              </div>
            </div>
            
            <div className="w-full md:w-1/2 p-10 md:p-16 overflow-y-auto">
              <div className="w-20 h-2 bg-odtu-red mb-10 rounded-full" />
              <p className="text-xl md:text-2xl font-bold text-slate-900 mb-8 leading-tight">
                {selectedGroup.description}
              </p>
              <div className="prose prose-slate max-w-none">
                <p className="text-slate-600 leading-relaxed text-lg">
                  {selectedGroup.longDescription || "Detaylı bilgi için lütfen derneğimizle iletişime geçin."}
                </p>
              </div>
              <button 
                className="mt-12 px-10 py-5 bg-slate-900 text-white rounded-full font-black text-sm hover:bg-odtu-red transition-all flex items-center gap-3 uppercase tracking-widest"
                onClick={() => onNavigate(ViewState.CONTACT)}
              >
                BİLGİ AL VE KATIL
                <ArrowRight size={20} />
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Hero Section */}
      <section className="relative py-32 overflow-hidden bg-slate-900">
        <div className="absolute inset-0 opacity-20 pointer-events-none">
          <div className="absolute top-0 right-0 w-[600px] h-[600px] bg-odtu-red rounded-full blur-[180px]" />
          <div className="absolute bottom-0 left-0 w-[600px] h-[600px] bg-odtu-blue rounded-full blur-[180px]" />
        </div>
        
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h1 className="text-6xl md:text-9xl font-black mb-6 tracking-tighter uppercase animate-slide-up">
            <span className="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-orange-600">
              MERHABA HOCAM!
            </span>
          </h1>
          <div className="max-w-4xl mx-auto mb-10">
            <p className="text-xl md:text-2xl text-white leading-relaxed font-bold animate-fade-in">
              İstanbul’un dinamizminde ODTÜ ruhunu, dayanışmasını ve kültürünü yaşatan topluluğumuza hoş geldin.
            </p>
          </div>
          <div className="flex items-center justify-center gap-3 text-odtu-red animate-fade-in delay-500">
            <Heart size={32} className="fill-current" />
            <span className="text-4xl md:text-6xl font-handwriting">Dayanışma gücümüzdür.</span>
          </div>
        </div>
      </section>

      {/* Persistent Sub-menu Navigation */}
      <section className="sticky top-[64px] md:top-[72px] z-40 bg-white/95 backdrop-blur shadow-sm border-b border-gray-100">
        <div className="max-w-7xl mx-auto px-4 overflow-x-auto hide-scrollbar">
          <div className="flex justify-center min-w-max">
            {TABS.map((tab) => (
              <button
                key={tab.id}
                onClick={() => handleTabChange(tab.id)}
                className={`flex items-center gap-2 px-8 py-6 text-xs font-black transition-all border-b-4 uppercase tracking-[0.2em] whitespace-nowrap ${
                  activeTab === tab.id
                    ? 'border-odtu-red text-odtu-red'
                    : 'border-transparent text-slate-400 hover:text-slate-800'
                }`}
              >
                {tab.icon}
                {tab.label}
              </button>
            ))}
          </div>
        </div>
      </section>

      <main className="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
        {activeTab === 'doing' && (
          <div className="animate-fade-in max-w-4xl mx-auto">
            <div className="space-y-12">
              <h2 className="text-4xl md:text-6xl font-black text-slate-900 uppercase tracking-tighter">Etkileşimi Güçlendiren Bir Köprü</h2>
              <div className="space-y-8">
                <p className="text-slate-600 text-xl md:text-2xl leading-relaxed">
                  Üyelerimizi, gönüllülerimizi, bursiyerlarimizi ve tüm destekçilerimizi aynı dayanışma ağında buluşturarak, İstanbul’daki ODTÜ topluluğunu bir arada tutuyoruz. 
                </p>
              </div>
              
              <div className="p-12 bg-odtu-blue/5 rounded-[4rem] border-l-[12px] border-odtu-red shadow-inner mt-16">
                 <p className="text-3xl md:text-4xl font-black text-slate-900 italic leading-tight uppercase">
                   "Bütün bu faydayı, Çalışma Gruplarımızın gönüllü katkıları ile devam ettiriyoruz."
                 </p>
              </div>
            </div>
            <PaginationButtons />
          </div>
        )}

        {activeTab === 'groups' && (
          <div className="animate-fade-in space-y-24">
            <div className="text-center max-w-3xl mx-auto mb-16">
               <h3 className="text-4xl md:text-6xl font-black text-slate-900 mb-6 uppercase tracking-tighter">ÇALIŞMA GRUPLARIMIZ</h3>
               <p className="text-slate-500 font-medium">Uzmanlık alanlarınıza veya hobilerinize göre ayrılmış gruplarımızda birlikte üretiyoruz. Detaylı bilgi için kartlara tıklayın Hocam.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {WORKING_GROUPS.map(group => (
                <div 
                  key={group.id} 
                  onClick={() => setSelectedGroup(group)}
                  className="group bg-white rounded-[2.5rem] overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 border border-slate-100 cursor-pointer flex flex-col"
                >
                  <div className="h-64 overflow-hidden relative">
                    <img src={group.image} alt={group.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                    <div className={`absolute inset-0 ${group.color || 'bg-odtu-red'} opacity-0 group-hover:opacity-20 transition-opacity`} />
                    <div className="absolute top-6 left-6 px-4 py-2 bg-white/90 backdrop-blur rounded-full text-[10px] font-black uppercase tracking-widest text-slate-900 shadow-sm">
                      KEŞFET
                    </div>
                  </div>
                  <div className="p-8 flex-1 flex flex-col">
                    <h4 className="text-2xl font-black text-slate-900 uppercase mb-4 group-hover:text-odtu-red transition-colors">{group.title}</h4>
                    <p className="text-slate-500 text-sm leading-relaxed mb-8 flex-1 font-medium">{group.description}</p>
                    <div className="mt-auto flex items-center gap-2 text-odtu-red font-black text-xs uppercase tracking-widest">
                      DETAYLI İNCELE <ArrowRight size={14} className="group-hover:translate-x-1 transition-transform" />
                    </div>
                  </div>
                </div>
              ))}
            </div>
            <PaginationButtons />
          </div>
        )}

        {activeTab === 'join' && (
          <div className="animate-fade-in text-center max-w-5xl mx-auto py-12">
            <h2 className="text-4xl md:text-7xl font-black text-slate-900 mb-10 uppercase tracking-tighter">ODTÜ RUHUNU BİRLİKTE <span className="text-odtu-blue">YAŞATALIM</span></h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-8">
               <button onClick={() => window.open('https://fonzip.com/odtumist/uyelik', '_blank')} className="group flex flex-col items-center gap-8 p-14 bg-white rounded-[4rem] border-2 border-slate-100 hover:border-odtu-blue hover:shadow-2xl transition-all">
                 <div className="w-24 h-24 bg-odtu-blue/10 text-odtu-blue rounded-[2rem] flex items-center justify-center group-hover:bg-odtu-blue group-hover:text-white transition-all">
                    <Users size={48} />
                 </div>
                 <div>
                   <h4 className="text-3xl font-black text-slate-900 mb-4 uppercase">ÜYE OL</h4>
                 </div>
               </button>
               <button className="group flex flex-col items-center gap-8 p-14 bg-white rounded-[4rem] border-2 border-slate-100 hover:border-odtu-red hover:shadow-2xl transition-all">
                 <div className="w-24 h-24 bg-odtu-red/10 text-odtu-red rounded-[2rem] flex items-center justify-center group-hover:bg-odtu-red group-hover:text-white transition-all">
                    <Heart size={48} />
                 </div>
                 <div>
                   <h4 className="text-3xl font-black text-slate-900 mb-4 uppercase">GÖNÜLLÜ OL</h4>
                 </div>
               </button>
            </div>
            <PaginationButtons />
          </div>
        )}

        {activeTab === 'history' && (
          <div className="animate-fade-in space-y-20 max-w-7xl mx-auto">
            {/* Intro Header */}
            <div className="bg-slate-900 rounded-[4rem] p-12 md:p-20 text-white shadow-2xl relative overflow-hidden">
               <div className="absolute top-0 right-0 w-96 h-96 bg-odtu-red/10 blur-[100px]" />
               <div className="relative z-10 max-w-4xl">
                 <h2 className="text-4xl md:text-6xl font-black mb-8 uppercase tracking-tighter">BİR MEŞALENİN <br /> İSTANBUL YOLCULUĞU</h2>
                 <p className="text-xl md:text-3xl font-bold leading-relaxed text-slate-200">
                   ODTÜMİST 1986 yılında İstanbul’daki ODTÜ’lülerin emekleriyle şube olarak yolculuğuna başladı ve 2001 yılında bağımsız bir dernek oldu. 
                   <span className="text-odtu-red"> 40 yıldır,</span> binlerce gönüllünün ve dayanışmanın enerjisiyle; burstan mentorluğa, şenliklerden mezun buluşmalarına "ODTÜ Ruhu"nu İstanbul’un kalbinde yaşatıyoruz.
                 </p>
               </div>
            </div>

            {/* Grid: Biliyor muydunuz? */}
            <section className="space-y-12">
               <div className="flex items-center gap-4 mb-12">
                  <div className="w-16 h-16 bg-odtu-blue rounded-2xl flex items-center justify-center text-white">
                    <HelpCircle size={32} />
                  </div>
                  <h3 className="text-3xl md:text-5xl font-black text-slate-900 uppercase tracking-tighter">BİLİYOR MUYDUNUZ?</h3>
               </div>
               
               <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                 {HISTORY_FACTS.map((fact, idx) => (
                   <div key={idx} className={`group bg-white rounded-[3rem] overflow-hidden border-4 ${fact.color} shadow-xl flex flex-col h-full`}>
                     <div className="h-64 overflow-hidden relative">
                        <img src={fact.image} alt={fact.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
                        <div className="absolute bottom-6 left-8">
                           <h4 className="text-2xl font-black text-white uppercase tracking-tight leading-tight">{fact.title}</h4>
                        </div>
                     </div>
                     <div className="p-8 md:p-10 flex-1 flex flex-col justify-center">
                        <p className="text-slate-600 text-lg md:text-xl font-medium leading-relaxed">
                          {fact.desc}
                        </p>
                     </div>
                   </div>
                 ))}
               </div>
            </section>

            <PaginationButtons />
          </div>
        )}

        {activeTab === 'management' && (
          <div className="animate-fade-in max-w-5xl mx-auto space-y-16">
            <div className="bg-slate-900 rounded-[3rem] p-10 md:p-14 text-white shadow-xl relative overflow-hidden mb-20">
               <div className="absolute top-0 right-0 w-64 h-64 bg-odtu-blue/10 blur-[80px]" />
               <h2 className="text-4xl md:text-7xl font-black mb-6 uppercase tracking-tighter animate-slide-up">YÖNETİM</h2>
               <p className="text-xl md:text-2xl font-bold text-slate-300 leading-relaxed">
                 Mevcut ve geçmiş dernek yönetimleri, gönüllülerimizden oluşan çalışma gruplarımız, dernek tüzüğümüz ve yönetmeliklerimiz ile faaliyet raporlarımızı görüntülemek için tıklayın.
               </p>
            </div>

            <div className="grid grid-cols-1 gap-8">
              {/* Yönetim Organları */}
              <div 
                onClick={() => onNavigate(ViewState.MANAGEMENT_BOARD)}
                className="bg-white border-2 border-slate-100 rounded-[3rem] p-8 md:p-12 transition-all hover:shadow-2xl hover:border-odtu-red group cursor-pointer flex flex-col md:flex-row items-center gap-10"
              >
                 <div className="w-24 h-24 bg-odtu-blue rounded-[2rem] flex items-center justify-center text-white shrink-0 group-hover:scale-110 transition-transform">
                   <ShieldCheck size={40} />
                 </div>
                 <div className="flex-1 text-center md:text-left">
                   <h4 className="font-black text-3xl text-slate-900 uppercase mb-2">Dernek Yönetim Organları</h4>
                   <p className="text-slate-500 font-medium">Yönetim Kurulu, Denetleme Kurulu, Disiplin Kurulu ve Danışma Kurulu üyelerimizin biyografileri.</p>
                 </div>
                 <ArrowRight className="text-slate-200 group-hover:text-odtu-red transition-all" size={48} />
              </div>

              {/* Çalışma Gruplarımız */}
              <div 
                onClick={() => handleTabChange('groups')}
                className="bg-white border-2 border-slate-100 rounded-[3rem] p-8 md:p-12 transition-all hover:shadow-2xl hover:border-odtu-blue group cursor-pointer flex flex-col md:flex-row items-center gap-10"
              >
                 <div className="w-24 h-24 bg-odtu-red rounded-[2rem] flex items-center justify-center text-white shrink-0 group-hover:scale-110 transition-transform">
                   <Target size={40} />
                 </div>
                 <div className="flex-1 text-center md:text-left">
                   <h4 className="font-black text-3xl text-slate-900 uppercase mb-2">Çalışma Gruplarımız</h4>
                   <p className="text-slate-500 font-medium">Derneğimizi yaşatan yaklaşık 15 çalışma grubumuzun sonsuz desteğiyle büyüyoruz. Onları yakından tanıyın.</p>
                 </div>
                 <ArrowRight className="text-slate-200 group-hover:text-odtu-blue transition-all" size={48} />
              </div>

              {/* Geçmiş Yönetimler */}
              <div 
                onClick={() => onNavigate(ViewState.MANAGEMENT_PAST)}
                className="bg-white border-2 border-slate-100 rounded-[3rem] p-8 md:p-12 transition-all hover:shadow-2xl hover:border-slate-900 group cursor-pointer flex flex-col md:flex-row items-center gap-10"
              >
                 <div className="w-24 h-24 bg-slate-900 rounded-[2rem] flex items-center justify-center text-white shrink-0 group-hover:scale-110 transition-transform">
                   <History size={40} />
                 </div>
                 <div className="flex-1 text-center md:text-left">
                   <h4 className="font-black text-3xl text-slate-900 uppercase mb-2">Geçmiş Yönetimler</h4>
                   <p className="text-slate-500 font-medium">1986'dan bugüne derneğimize emek vermiş tüm kurullarımız ve yöneticilerimiz.</p>
                 </div>
                 <ArrowRight className="text-slate-200 group-hover:text-slate-900 transition-all" size={48} />
              </div>

              {/* Tüzük ve Yönetmelikler */}
              <div 
                onClick={() => onNavigate(ViewState.DOCUMENTS)}
                className="bg-white border-2 border-slate-100 rounded-[3rem] p-8 md:p-12 transition-all hover:shadow-2xl hover:border-odtu-blue group cursor-pointer flex flex-col md:flex-row items-center gap-10"
              >
                 <div className="w-24 h-24 bg-odtu-blue/10 rounded-[2rem] flex items-center justify-center text-odtu-blue shrink-0 group-hover:bg-odtu-blue group-hover:text-white transition-all">
                   <FileText size={40} />
                 </div>
                 <div className="flex-1 text-center md:text-left">
                   <h4 className="font-black text-3xl text-slate-900 uppercase mb-2">Dernek Tüzüğü ve Yönetmelikler</h4>
                   <p className="text-slate-500 font-medium">Şeffaf yönetişim ilkelerimiz, tüzüğümüz ve çalışma yönetmeliklerimiz.</p>
                 </div>
                 <ArrowRight className="text-slate-200 group-hover:text-odtu-blue transition-all" size={48} />
              </div>

              {/* Faaliyet Raporları */}
              <div 
                onClick={() => onNavigate(ViewState.DOCUMENTS)}
                className="bg-white border-2 border-slate-100 rounded-[3rem] p-8 md:p-12 transition-all hover:shadow-2xl hover:border-odtu-red group cursor-pointer flex flex-col md:flex-row items-center gap-10"
              >
                 <div className="w-24 h-24 bg-odtu-red/10 rounded-[2rem] flex items-center justify-center text-odtu-red shrink-0 group-hover:bg-odtu-red group-hover:text-white transition-all">
                   <Briefcase size={40} />
                 </div>
                 <div className="flex-1 text-center md:text-left">
                   <h4 className="font-black text-3xl text-slate-900 uppercase mb-2">Faaliyet Raporları</h4>
                   <p className="text-slate-500 font-medium">Yıllık çalışma raporlarımız, mali tablolarımız ve kurumsal başarı hikayelerimiz.</p>
                 </div>
                 <ArrowRight className="text-slate-200 group-hover:text-odtu-red transition-all" size={48} />
              </div>
            </div>
            <PaginationButtons />
          </div>
        )}
      </main>
    </div>
  );
};

export default AboutPage;
