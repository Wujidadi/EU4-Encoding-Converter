// Ported from https://github.com/matanki-saito/EU4SpecialEscape/blob/master/ConsoleApplication3/ConsoleApplication3.cpp#L235
module.exports = function specialEscape(char, toUtf8, newVersion) {
  // DO NOT escape valid char
  if (char.codePointAt(0) < 256) {
    return char
  }
  const hex = char.codePointAt(0).toString(16)
  // default if UTF8, so hex is 4 length for most letters
  // split low byte and high byte
  // transform decimal to hex
  let low = parseInt(hex.slice(-2), 16) // last 2 byte
  let high = parseInt(hex.slice(0, 2), 16) // first 2 bytes

  // Magic numbers
  let lowByteOffset = 15
  const highByteOffset = -9

  const internalChars = [
    0x00, 0x0A, 0x0D,
    0x20,
    0x22, 0x24,
    0x40, 0x5B, 0x5C,
    0x7B, 0x7D, 0x7E, 0x80,
    0xA3, 0xA4, 0xA7, 0xBD,
    0x3B, // ;
    0x5D, // ]
    0x5F, // _
    0x3D, // =
    0x23 // #
  ]

  if (toUtf8) {
    internalChars.push(0x2F) // backslash (/) Will cause province name error if not escaped
    if (!newVersion) {
      // 0x20 in oldVersion escape will be transform to backslash which will not be parsed right by the engine
      // So it has to be removed
      // Will be transformed to 0x2f (backslash) in high byte, remove it
      internalChars.splice(internalChars.indexOf(0x20), 1)
    }
  }

  if (newVersion) {
    // After EU4 1.26 the rule has changed
    // Yet CK2 always uses the old rule
    lowByteOffset = 14
  }

  let escapeChr = 0x10
  // because characters in internalChars will be used in game as special characters, such as csv delimiter
  // so we have to escape these characters
  // 0x10 0x11 0x12 0x13 are all leading character to determine a multibyte letter start, depends on how the escape works
  if (internalChars.includes(high)) {
    escapeChr += 2
  }
  if (internalChars.includes(low)) {
    escapeChr++
  }

  // Make the escape so the characters will be parsed by engine correctly
  // Copy from https://github.com/matanki-saito/EU4SpecialEscape/blob/ea331f4633ca6b8e7661bf3a1973242ef4e021d2/ConsoleApplication3/ConsoleApplication3.cpp#L259
  switch (escapeChr) {
    case 0x11:
      low += lowByteOffset
      break
    case 0x12:
      high += highByteOffset
      break
    case 0x13:
      low += lowByteOffset
      high += highByteOffset
      break
    case 0x10:
    default:
      break
  }

  if (toUtf8) {
    // For EU4
    // Transform Latin1 extended control characters to utf8
    // Chars in this section will not display correctly in utf8 encoding
    low = cp1252ToUtf8(low)
    high = cp1252ToUtf8(high)
  }

  // Convert the hex codePoint back to actually character
  return [escapeChr, low, high].map(c => String.fromCodePoint(c)).join('')
}

function cp1252ToUtf8(char) {
  const escapeList = {
    0x80: 0x20AC, 0x82: 0x201A, 0x83: 0x0192, 0x84: 0x201E,
    0x85: 0x2026, 0x86: 0x2020, 0x87: 0x2021, 0x88: 0x02C6,
    0x89: 0x2030, 0x8A: 0x0160, 0x8B: 0x2039, 0x8C: 0x0152,
    0x8E: 0x017D, 0x91: 0x2018, 0x92: 0x2019, 0x93: 0x201C,
    0x94: 0x201D, 0x95: 0x2022, 0x96: 0x2013, 0x97: 0x2014,
    0x98: 0x02DC, 0x99: 0x2122, 0x9A: 0x0161, 0x9B: 0x203A,
    0x9C: 0x0153, 0x9E: 0x017E, 0x9F: 0x0178
  }
  return escapeList[char] || char
}
