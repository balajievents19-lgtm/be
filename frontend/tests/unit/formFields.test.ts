import assert from 'node:assert/strict'
import { describe, it } from 'node:test'
import {
  buildProgramLocationLabel,
  formatEventDateDisplay,
  isExactTenDigitMobile,
  isPastEventDate,
  isValidEnquiryPhone,
  sanitizeIndianMobileDigits
} from '../../app/utils/formFields.ts'

describe('formFields utils', () => {
  it('formats ISO dates for display', () => {
    assert.equal(formatEventDateDisplay('2030-12-20'), '20-12-2030')
    assert.equal(formatEventDateDisplay(''), '')
    assert.equal(formatEventDateDisplay('bad'), '')
  })

  it('detects past dates against local calendar day', () => {
    const now = new Date(2026, 7, 14)
    assert.equal(isPastEventDate('2026-08-13', now), true)
    assert.equal(isPastEventDate('2026-08-14', now), false)
    assert.equal(isPastEventDate('2026-08-15', now), false)
  })

  it('builds program location labels from CMS parts', () => {
    assert.equal(
      buildProgramLocationLabel({
        name: 'Head Office',
        address: 'Shop No.15',
        city: 'Jhunjhunu',
        state: 'Rajasthan',
        pincode: '333001'
      }),
      'Head Office — Shop No.15 — Jhunjhunu, Rajasthan, 333001'
    )
    assert.equal(buildProgramLocationLabel({ address: 'Only Address' }), 'Only Address')
  })

  it('accepts formatted Indian mobile numbers', () => {
    assert.equal(isValidEnquiryPhone('9876543210'), true)
    assert.equal(isValidEnquiryPhone('+91 98765 43210'), true)
    assert.equal(isValidEnquiryPhone('12345'), false)
    assert.equal(isValidEnquiryPhone('abcdefghij'), false)
  })

  it('accepts only exact 10-digit Indian mobile numbers', () => {
    assert.equal(isExactTenDigitMobile('9876543210'), true)
    assert.equal(isExactTenDigitMobile('0123456789'), true)
    assert.equal(isExactTenDigitMobile('abcdefghij'), false)
    assert.equal(isExactTenDigitMobile('987654321'), false)
    assert.equal(isExactTenDigitMobile('98765432101'), false)
    assert.equal(isExactTenDigitMobile('98765 43210'), false)
    assert.equal(isExactTenDigitMobile('+919876543210'), false)
  })

  it('sanitizes mobile input to digits only, max 10', () => {
    assert.equal(sanitizeIndianMobileDigits('ab98cd765ef43210xyz'), '9876543210')
    assert.equal(sanitizeIndianMobileDigits('98765 43210'), '9876543210')
    assert.equal(sanitizeIndianMobileDigits('9876543210123'), '9876543210')
    assert.equal(sanitizeIndianMobileDigits('phone'), '')
  })
})
