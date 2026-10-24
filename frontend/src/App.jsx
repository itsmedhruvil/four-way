import { useEffect } from 'react'
import Header from './components/Header.jsx'
import Hero from './components/Hero.jsx'
import TrustBadges from './components/TrustBadges.jsx'
import About from './components/About.jsx'
import Services from './components/Services.jsx'
import WhyChooseUs from './components/WhyChooseUs.jsx'
import Process from './components/Process.jsx'
import Projects from './components/Projects.jsx'
import Testimonials from './components/Testimonials.jsx'
import Faq from './components/Faq.jsx'
import CtaBanner from './components/CtaBanner.jsx'
import Blog from './components/Blog.jsx'
import Newsletter from './components/Newsletter.jsx'
import Footer from './components/Footer.jsx'
import { QuoteProvider } from './context/QuoteContext.jsx'
import { useSiteContent } from './hooks/useSiteContent.js'

function useDocumentMeta(seo) {
  useEffect(() => {
    if (seo?.title) document.title = seo.title
    if (!seo?.description) return

    let meta = document.querySelector('meta[name="description"]')
    if (!meta) {
      meta = document.createElement('meta')
      meta.setAttribute('name', 'description')
      document.head.appendChild(meta)
    }
    meta.setAttribute('content', seo.description)
  }, [seo])
}

export default function App() {
  const { content, source, status } = useSiteContent()
  const { settings } = content
  useDocumentMeta(settings.seo)

  return (
    <QuoteProvider settings={settings}>
      <div data-content-source={source} data-content-status={status}>
        <Header settings={settings} />
        <main>
          <Hero hero={settings.hero} />
          <TrustBadges badges={content.badges} />
          <About about={settings.about} />
          <Services heading={settings.servicesSection} services={content.services} />
          <WhyChooseUs heading={settings.reasonsSection} reasons={content.reasons} />
          <Process heading={settings.processSection} steps={content.steps} />
          <Projects heading={settings.projectsSection} projects={content.projects} />
          <Testimonials
            heading={settings.testimonialsSection}
            testimonials={content.testimonials}
          />
          <Faq faqs={content.faqs} heading={settings.faqSection} />
          <CtaBanner cta={settings.ctaSection} />
          <Blog heading={settings.blogSection} posts={content.posts} />
          <Newsletter newsletter={settings.newsletter} />
        </main>
        <Footer settings={settings} />
      </div>
    </QuoteProvider>
  )
}
