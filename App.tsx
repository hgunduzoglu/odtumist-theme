
import React, { useState } from 'react';
import Navbar from './components/Navbar';
import Hero from './components/Hero';
import EventsModule from './components/EventsModule';
import MembershipCTAs from './components/MembershipCTAs';
import WorkingGroups from './components/WorkingGroups';
import GroupPhotoSection from './components/GroupPhotoSection';
import Footer from './components/Footer';
import AIAssistant from './components/AIAssistant';
import AboutPage from './components/AboutPage';
import MembershipPage from './components/MembershipPage';
import ContactPage from './components/ContactPage';
import EventsPage from './components/EventsPage';
import SolidarityPage from './components/SolidarityPage';
import { ViewState } from './types';

function App() {
  const [activeView, setActiveView] = useState<ViewState>(ViewState.HOME);
  const [viewParams, setViewParams] = useState<any>(null);

  const handleNavigate = (view: ViewState, params?: any) => {
    setActiveView(view);
    setViewParams(params);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const renderHome = () => (
    <>
      <Hero onNavigate={handleNavigate} />
      <EventsModule onNavigate={handleNavigate} />
      <MembershipCTAs />
      <WorkingGroups />
      <GroupPhotoSection />
    </>
  );

  const isMembershipView = activeView.startsWith('MEMBERSHIP');

  return (
    <div className="flex flex-col min-h-screen">
      <Navbar activeView={activeView} onNavigate={handleNavigate} />
      
      <main className="flex-grow">
        {activeView === ViewState.HOME && renderHome()}
        {activeView === ViewState.ABOUT && <AboutPage onNavigate={handleNavigate} initialTab={viewParams?.tab} />}
        {activeView === ViewState.EVENTS && <EventsPage />}
        {activeView === ViewState.SOLIDARITY && <SolidarityPage initialSection={viewParams?.section} />}
        {activeView === ViewState.CONTACT && <ContactPage />}
        
        {isMembershipView && (
          <MembershipPage view={activeView} onNavigate={handleNavigate} />
        )}

        {/* Kurumsal Yönetim ve Belgeler Placeholder'lar */}
        {activeView === ViewState.MANAGEMENT_BOARD && (
          <div className="pt-32 pb-20 bg-slate-50 min-h-screen">
            <div className="max-w-7xl mx-auto px-4 text-center">
              <h1 className="text-4xl md:text-6xl font-black mb-10">YÖNETİM ORGANLARI</h1>
              <p className="text-xl text-slate-500">Bu sayfa biyografilerle birlikte hazırlanmaktadır Hocam...</p>
              <button onClick={() => handleNavigate(ViewState.ABOUT, {tab: 'management'})} className="mt-10 px-8 py-4 bg-slate-900 text-white rounded-full font-bold">Geri Dön</button>
            </div>
          </div>
        )}

        {activeView === ViewState.MANAGEMENT_PAST && (
          <div className="pt-32 pb-20 bg-slate-50 min-h-screen">
            <div className="max-w-7xl mx-auto px-4 text-center">
              <h1 className="text-4xl md:text-6xl font-black mb-10">GEÇMİŞ YÖNETİMLER</h1>
              <p className="text-xl text-slate-500">Tarihsel yönetim listesi hazırlanmaktadır Hocam...</p>
              <button onClick={() => handleNavigate(ViewState.ABOUT, {tab: 'management'})} className="mt-10 px-8 py-4 bg-slate-900 text-white rounded-full font-bold">Geri Dön</button>
            </div>
          </div>
        )}

        {activeView === ViewState.DOCUMENTS && (
          <div className="pt-32 pb-20 bg-slate-50 min-h-screen">
            <div className="max-w-7xl mx-auto px-4 text-center">
              <h1 className="text-4xl md:text-6xl font-black mb-10">KURUMSAL BELGELER</h1>
              <p className="text-xl text-slate-500">Tüzük, yönetmelik ve faaliyet raporları arşivi hazırlanmaktadır Hocam...</p>
              <button onClick={() => handleNavigate(ViewState.ABOUT, {tab: 'management'})} className="mt-10 px-8 py-4 bg-slate-900 text-white rounded-full font-bold">Geri Dön</button>
            </div>
          </div>
        )}
      </main>

      <AIAssistant />
      <Footer />
    </div>
  );
}

export default App;
