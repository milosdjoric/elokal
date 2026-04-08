interface SchemaOrgProduct {
  name: string
  description?: string
  image?: string[]
  sku?: string
  url: string
  price: string
  salePrice?: string
  currency?: string
  availability: 'InStock' | 'OutOfStock'
  ratingValue?: number
  reviewCount?: number
}

interface BreadcrumbItem {
  name: string
  url: string
}

export function useSchemaOrg() {
  function injectJsonLd(data: Record<string, unknown>) {
    useHead({
      script: [
        {
          type: 'application/ld+json',
          innerHTML: JSON.stringify(data),
        },
      ],
    })
  }

  function setProductSchema(product: SchemaOrgProduct) {
    const schema: Record<string, unknown> = {
      '@context': 'https://schema.org',
      '@type': 'Product',
      name: product.name,
      url: product.url,
      offers: {
        '@type': 'Offer',
        price: product.salePrice || product.price,
        priceCurrency: product.currency || 'RSD',
        availability: product.availability === 'InStock'
          ? 'https://schema.org/InStock'
          : 'https://schema.org/OutOfStock',
        url: product.url,
      },
    }

    if (product.description) schema.description = product.description
    if (product.sku) schema.sku = product.sku
    if (product.image?.length) schema.image = product.image

    if (product.ratingValue && product.reviewCount) {
      schema.aggregateRating = {
        '@type': 'AggregateRating',
        ratingValue: product.ratingValue,
        reviewCount: product.reviewCount,
      }
    }

    injectJsonLd(schema)
  }

  function setBreadcrumbSchema(items: BreadcrumbItem[]) {
    const schema = {
      '@context': 'https://schema.org',
      '@type': 'BreadcrumbList',
      itemListElement: items.map((item, index) => ({
        '@type': 'ListItem',
        position: index + 1,
        name: item.name,
        item: item.url,
      })),
    }

    injectJsonLd(schema)
  }

  function setOrganizationSchema(org: { name: string; url: string; logo?: string; phone?: string; email?: string }) {
    const schema: Record<string, unknown> = {
      '@context': 'https://schema.org',
      '@type': 'Organization',
      name: org.name,
      url: org.url,
    }

    if (org.logo) schema.logo = org.logo
    if (org.phone) schema.telephone = org.phone
    if (org.email) schema.email = org.email

    injectJsonLd(schema)
  }

  function setFaqSchema(faqs: { question: string; answer: string }[]) {
    if (faqs.length === 0) return

    const schema = {
      '@context': 'https://schema.org',
      '@type': 'FAQPage',
      mainEntity: faqs.map(faq => ({
        '@type': 'Question',
        name: faq.question,
        acceptedAnswer: {
          '@type': 'Answer',
          text: faq.answer,
        },
      })),
    }

    injectJsonLd(schema)
  }

  return { setProductSchema, setBreadcrumbSchema, setOrganizationSchema, setFaqSchema }
}
