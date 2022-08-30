module.exports = function decodeString(string, encoding, game) {
  // string has to be utf8
  let content = ''
  for (let i = 0; i < string.length; i++) {
    const char = string[i].codePointAt(0)
    if (char >= 0x10 && char <= 0x13) {
      // with leading control character
      let high = string[i + 2].codePointAt(0) // high byte
      let low = string[i + 1].codePointAt(0) // low byte

      let lowByteOffset = 15
      const highByteOffset = -9

      if (game === 'eu4') {
        lowByteOffset = 14
      }
      // some of the chars has been converted during encode, we need to convert it back
      high = UCS2ToCP1252(high)
      low = UCS2ToCP1252(low)

      i += 2
      // convert characters back
      switch (char) {
        case 0x11:
          low -= lowByteOffset
          break
        case 0x12:
          high -= highByteOffset
          break
        case 0x13:
          low -= lowByteOffset
          high -= highByteOffset
          break
        case 0x10:
        default:
          break
      }

      // back to utf8
      // eg. 大 -> 0x10 0x59 0x27
      // the backward process will be String.fromCodePoint(0x5927)
      const codePoint = [high, low]
        .map(l => parseInt(l).toString(16)) // convert char to hex
        .map(l => ('0' + l).slice(-2)) // left pad hex with 0
        .join('')

      content += String.fromCodePoint(parseInt(codePoint, 16))
    } else {
      // skip if char is within latin1 range
      content += string[i]
    }
  }
  return content
}

const charMap = {
  0x20AC: 0x80,
  0x201A: 0x82,
  0x0192: 0x83,
  0x201E: 0x84,
  0x2026: 0x85,
  0x2020: 0x86,
  0x2021: 0x87,
  0x02C6: 0x88,
  0x2030: 0x89,
  0x0160: 0x8A,
  0x2039: 0x8B,
  0x0152: 0x8C,
  0x017D: 0x8E,
  0x2018: 0x91,
  0x2019: 0x92,
  0x201C: 0x93,
  0x201D: 0x94,
  0x2022: 0x95,
  0x2013: 0x96,
  0x2014: 0x97,
  0x02DC: 0x98,
  0x2122: 0x99,
  0x0161: 0x9A,
  0x203A: 0x9B,
  0x0153: 0x9C,
  0x017E: 0x9E,
  0x0178: 0x9F
}

function UCS2ToCP1252(cp) {
  return charMap[cp] || cp
}