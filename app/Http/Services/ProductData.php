<?php

namespace App\Http\Services;

class ProductData {
     private $products = [];
     
     public function __construct() {
         $this->initializeProducts();
     }
     
     private function initializeProducts() {
         $this->products = [
             // Elektronik Ürünler
             ['id' => 1, 'name' => 'iPhone 15 Pro Max', 'category' => 'Telefon', 'price' => 54999.99, 'brand' => 'Apple', 'rating' => 4.8, 'stock' => 45, 'image' => 'iphone15.jpg'],
             ['id' => 2, 'name' => 'Samsung Galaxy S24 Ultra', 'category' => 'Telefon', 'price' => 44999.99, 'brand' => 'Samsung', 'rating' => 4.7, 'stock' => 32, 'image' => 'galaxy-s24.jpg'],
             ['id' => 3, 'name' => 'MacBook Air M3', 'category' => 'Bilgisayar', 'price' => 39999.99, 'brand' => 'Apple', 'rating' => 4.9, 'stock' => 28, 'image' => 'macbook-air.jpg'],
             ['id' => 4, 'name' => 'Dell XPS 13', 'category' => 'Bilgisayar', 'price' => 32999.99, 'brand' => 'Dell', 'rating' => 4.6, 'stock' => 15, 'image' => 'dell-xps13.jpg'],
             ['id' => 5, 'name' => 'iPad Pro 12.9', 'category' => 'Tablet', 'price' => 29999.99, 'brand' => 'Apple', 'rating' => 4.8, 'stock' => 22, 'image' => 'ipad-pro.jpg'],
             ['id' => 6, 'name' => 'Sony WH-1000XM5', 'category' => 'Kulaklık', 'price' => 8999.99, 'brand' => 'Sony', 'rating' => 4.9, 'stock' => 67, 'image' => 'sony-wh1000xm5.jpg'],
             ['id' => 7, 'name' => 'AirPods Pro 2', 'category' => 'Kulaklık', 'price' => 5999.99, 'brand' => 'Apple', 'rating' => 4.7, 'stock' => 89, 'image' => 'airpods-pro2.jpg'],
             ['id' => 8, 'name' => 'Samsung 65" QLED TV', 'category' => 'Televizyon', 'price' => 24999.99, 'brand' => 'Samsung', 'rating' => 4.8, 'stock' => 12, 'image' => 'samsung-qled65.jpg'],
             ['id' => 9, 'name' => 'LG 55" OLED TV', 'category' => 'Televizyon', 'price' => 19999.99, 'brand' => 'LG', 'rating' => 4.9, 'stock' => 18, 'image' => 'lg-oled55.jpg'],
             ['id' => 10, 'name' => 'PlayStation 5', 'category' => 'Oyun Konsolu', 'price' => 15999.99, 'brand' => 'Sony', 'rating' => 4.8, 'stock' => 8, 'image' => 'ps5.jpg'],
             
             // Giyim Ürünleri
             ['id' => 11, 'name' => 'Nike Air Max 270', 'category' => 'Spor Ayakkabı', 'price' => 2499.99, 'brand' => 'Nike', 'rating' => 4.6, 'stock' => 156, 'image' => 'nike-airmax270.jpg'],
             ['id' => 12, 'name' => 'Adidas Ultraboost 22', 'category' => 'Spor Ayakkabı', 'price' => 2999.99, 'brand' => 'Adidas', 'rating' => 4.7, 'stock' => 134, 'image' => 'adidas-ultraboost22.jpg'],
             ['id' => 13, 'name' => 'Levi\'s 501 Jeans', 'category' => 'Kot Pantolon', 'price' => 899.99, 'brand' => 'Levi\'s', 'rating' => 4.5, 'stock' => 234, 'image' => 'levis-501.jpg'],
             ['id' => 14, 'name' => 'Tommy Hilfiger Polo', 'category' => 'Polo Yaka', 'price' => 699.99, 'brand' => 'Tommy Hilfiger', 'rating' => 4.4, 'stock' => 189, 'image' => 'tommy-polo.jpg'],
             ['id' => 15, 'name' => 'Zara Blazer Ceket', 'category' => 'Ceket', 'price' => 1299.99, 'brand' => 'Zara', 'rating' => 4.3, 'stock' => 67, 'image' => 'zara-blazer.jpg'],
             ['id' => 16, 'name' => 'H&M Elbise', 'category' => 'Elbise', 'price' => 399.99, 'brand' => 'H&M', 'rating' => 4.2, 'stock' => 298, 'image' => 'hm-elbise.jpg'],
             ['id' => 17, 'name' => 'Mango Gömlek', 'category' => 'Gömlek', 'price' => 299.99, 'brand' => 'Mango', 'rating' => 4.4, 'stock' => 178, 'image' => 'mango-gomlek.jpg'],
             ['id' => 18, 'name' => 'Pull&Bear Sweatshirt', 'category' => 'Sweatshirt', 'price' => 199.99, 'brand' => 'Pull&Bear', 'rating' => 4.1, 'stock' => 345, 'image' => 'pullbear-sweatshirt.jpg'],
             ['id' => 19, 'name' => 'Bershka Etek', 'category' => 'Etek', 'price' => 249.99, 'brand' => 'Bershka', 'rating' => 4.0, 'stock' => 267, 'image' => 'bershka-etek.jpg'],
             ['id' => 20, 'name' => 'Stradivarius Çanta', 'category' => 'Çanta', 'price' => 399.99, 'brand' => 'Stradivarius', 'rating' => 4.3, 'stock' => 89, 'image' => 'stradivarius-canta.jpg'],
             
             // Ev & Yaşam
             ['id' => 21, 'name' => 'IKEA Malm Yatak', 'category' => 'Mobilya', 'price' => 2999.99, 'brand' => 'IKEA', 'rating' => 4.4, 'stock' => 23, 'image' => 'ikea-malm-yatak.jpg'],
             ['id' => 22, 'name' => 'Philips Hue Starter Kit', 'category' => 'Aydınlatma', 'price' => 1999.99, 'brand' => 'Philips', 'rating' => 4.6, 'stock' => 45, 'image' => 'philips-hue-kit.jpg'],
             ['id' => 23, 'name' => 'Dyson V15 Detect', 'category' => 'Elektrikli Süpürge', 'price' => 8999.99, 'brand' => 'Dyson', 'rating' => 4.8, 'stock' => 34, 'image' => 'dyson-v15.jpg'],
             ['id' => 24, 'name' => 'Bosch Çamaşır Makinesi', 'category' => 'Beyaz Eşya', 'price' => 12999.99, 'brand' => 'Bosch', 'rating' => 4.7, 'stock' => 19, 'image' => 'bosch-camasir.jpg'],
             ['id' => 25, 'name' => 'Siemens Bulaşık Makinesi', 'category' => 'Beyaz Eşya', 'price' => 8999.99, 'brand' => 'Siemens', 'rating' => 4.6, 'stock' => 26, 'image' => 'siemens-bulasik.jpg'],
             ['id' => 26, 'name' => 'Arçelik Buzdolabı', 'category' => 'Beyaz Eşya', 'price' => 15999.99, 'brand' => 'Arçelik', 'rating' => 4.5, 'stock' => 14, 'image' => 'arcelik-buzdolabi.jpg'],
             ['id' => 27, 'name' => 'Vestel Fırın', 'category' => 'Beyaz Eşya', 'price' => 3999.99, 'brand' => 'Vestel', 'rating' => 4.3, 'stock' => 38, 'image' => 'vestel-firin.jpg'],
             ['id' => 28, 'name' => 'Tefal Tava Seti', 'category' => 'Mutfak', 'price' => 899.99, 'brand' => 'Tefal', 'rating' => 4.4, 'stock' => 156, 'image' => 'tefal-tava-seti.jpg'],
             ['id' => 29, 'name' => 'Zwilling Bıçak Seti', 'category' => 'Mutfak', 'price' => 2499.99, 'brand' => 'Zwilling', 'rating' => 4.8, 'stock' => 67, 'image' => 'zwilling-bicak.jpg'],
             ['id' => 30, 'name' => 'KitchenAid Mikser', 'category' => 'Mutfak', 'price' => 5999.99, 'brand' => 'KitchenAid', 'rating' => 4.7, 'stock' => 23, 'image' => 'kitchenaid-mikser.jpg'],
             
             // Spor & Outdoor
             ['id' => 31, 'name' => 'Decathlon Bisiklet', 'category' => 'Bisiklet', 'price' => 3999.99, 'brand' => 'Decathlon', 'rating' => 4.3, 'stock' => 45, 'image' => 'decathlon-bisiklet.jpg'],
             ['id' => 32, 'name' => 'Salomon Trail Koşu Ayakkabısı', 'category' => 'Spor Ayakkabı', 'price' => 1899.99, 'brand' => 'Salomon', 'rating' => 4.6, 'stock' => 78, 'image' => 'salomon-trail.jpg'],
             ['id' => 33, 'name' => 'The North Face Mont', 'category' => 'Mont', 'price' => 3999.99, 'brand' => 'The North Face', 'rating' => 4.7, 'stock' => 34, 'image' => 'northface-mont.jpg'],
             ['id' => 34, 'name' => 'Columbia Ceket', 'category' => 'Ceket', 'price' => 2499.99, 'brand' => 'Columbia', 'rating' => 4.5, 'stock' => 56, 'image' => 'columbia-ceket.jpg'],
             ['id' => 35, 'name' => 'Patagonia Hırka', 'category' => 'Hırka', 'price' => 1899.99, 'brand' => 'Patagonia', 'rating' => 4.8, 'stock' => 42, 'image' => 'patagonia-hirka.jpg'],
             ['id' => 36, 'name' => 'Adidas Spor Çanta', 'category' => 'Spor Çanta', 'price' => 399.99, 'brand' => 'Adidas', 'rating' => 4.2, 'stock' => 189, 'image' => 'adidas-spor-canta.jpg'],
             ['id' => 37, 'name' => 'Nike Spor Çorap', 'category' => 'Spor Çorap', 'price' => 99.99, 'brand' => 'Nike', 'rating' => 4.1, 'stock' => 456, 'image' => 'nike-spor-corap.jpg'],
             ['id' => 38, 'name' => 'Under Armour Şort', 'category' => 'Şort', 'price' => 299.99, 'brand' => 'Under Armour', 'rating' => 4.3, 'stock' => 234, 'image' => 'ua-sort.jpg'],
             ['id' => 39, 'name' => 'Puma Spor Tshirt', 'category' => 'Spor Tshirt', 'price' => 199.99, 'brand' => 'Puma', 'rating' => 4.0, 'stock' => 345, 'image' => 'puma-spor-tshirt.jpg'],
             ['id' => 40, 'name' => 'Reebok Spor Pantolon', 'category' => 'Spor Pantolon', 'price' => 349.99, 'brand' => 'Reebok', 'rating' => 4.2, 'stock' => 178, 'image' => 'reebok-spor-pantolon.jpg'],
             
             // Kozmetik & Kişisel Bakım
             ['id' => 41, 'name' => 'L\'Oreal Paris Şampuan', 'category' => 'Şampuan', 'price' => 89.99, 'brand' => 'L\'Oreal', 'rating' => 4.3, 'stock' => 567, 'image' => 'loreal-sampuan.jpg'],
             ['id' => 42, 'name' => 'Garnier Yüz Temizleme Jeli', 'category' => 'Yüz Bakımı', 'price' => 69.99, 'brand' => 'Garnier', 'rating' => 4.2, 'stock' => 789, 'image' => 'garnier-yuz-jeli.jpg'],
             ['id' => 43, 'name' => 'Nivea Nemlendirici', 'category' => 'Nemlendirici', 'price' => 79.99, 'brand' => 'Nivea', 'rating' => 4.4, 'stock' => 456, 'image' => 'nivea-nemlendirici.jpg'],
             ['id' => 44, 'name' => 'Maybelline Ruj', 'category' => 'Makyaj', 'price' => 129.99, 'brand' => 'Maybelline', 'rating' => 4.1, 'stock' => 234, 'image' => 'maybelline-ruj.jpg'],
             ['id' => 45, 'name' => 'Essence Göz Farı', 'category' => 'Makyaj', 'price' => 49.99, 'brand' => 'Essence', 'rating' => 4.0, 'stock' => 678, 'image' => 'essence-goz-fari.jpg'],
             ['id' => 46, 'name' => 'Catrice Fondöten', 'category' => 'Makyaj', 'price' => 89.99, 'brand' => 'Catrice', 'rating' => 4.3, 'stock' => 345, 'image' => 'catrice-fondoten.jpg'],
             ['id' => 47, 'name' => 'Bioderma Sensibio', 'category' => 'Yüz Bakımı', 'price' => 199.99, 'brand' => 'Bioderma', 'rating' => 4.7, 'stock' => 123, 'image' => 'bioderma-sensibio.jpg'],
             ['id' => 48, 'name' => 'La Roche Posay Güneş Kremi', 'category' => 'Güneş Bakımı', 'price' => 249.99, 'brand' => 'La Roche Posay', 'rating' => 4.8, 'stock' => 89, 'image' => 'laroche-gunes.jpg'],
             ['id' => 49, 'name' => 'Vichy Mineral 89', 'category' => 'Serum', 'price' => 299.99, 'brand' => 'Vichy', 'rating' => 4.6, 'stock' => 67, 'image' => 'vichy-mineral89.jpg'],
             ['id' => 50, 'name' => 'Clinique Dramatically Different', 'category' => 'Nemlendirici', 'price' => 399.99, 'brand' => 'Clinique', 'rating' => 4.5, 'stock' => 45, 'image' => 'clinique-ddml.jpg'],
             
             // Kitap & Hobi
             ['id' => 51, 'name' => 'Harry Potter Set (7 Kitap)', 'category' => 'Kitap', 'price' => 299.99, 'brand' => 'Yapı Kredi', 'rating' => 4.9, 'stock' => 234, 'image' => 'harry-potter-set.jpg'],
             ['id' => 52, 'name' => 'Lord of the Rings Set', 'category' => 'Kitap', 'price' => 199.99, 'brand' => 'İthaki', 'rating' => 4.8, 'stock' => 189, 'image' => 'lotr-set.jpg'],
             ['id' => 53, 'name' => 'Game of Thrones Set', 'category' => 'Kitap', 'price' => 399.99, 'brand' => 'Epsilon', 'rating' => 4.7, 'stock' => 156, 'image' => 'got-set.jpg'],
             ['id' => 54, 'name' => 'Lego Star Wars Millennium Falcon', 'category' => 'Oyuncak', 'price' => 8999.99, 'brand' => 'Lego', 'rating' => 4.9, 'stock' => 12, 'image' => 'lego-millennium.jpg'],
             ['id' => 55, 'name' => 'Lego Harry Potter Hogwarts', 'category' => 'Oyuncak', 'price' => 5999.99, 'brand' => 'Lego', 'rating' => 4.8, 'stock' => 23, 'image' => 'lego-hogwarts.jpg'],
             ['id' => 56, 'name' => 'Barbie Dreamhouse', 'category' => 'Oyuncak', 'price' => 1999.99, 'brand' => 'Barbie', 'rating' => 4.6, 'stock' => 45, 'image' => 'barbie-dreamhouse.jpg'],
             ['id' => 57, 'name' => 'Hot Wheels Track Set', 'category' => 'Oyuncak', 'price' => 399.99, 'brand' => 'Hot Wheels', 'rating' => 4.3, 'stock' => 178, 'image' => 'hotwheels-track.jpg'],
             ['id' => 58, 'name' => 'Monopoly Oyunu', 'category' => 'Oyun', 'price' => 299.99, 'brand' => 'Hasbro', 'rating' => 4.5, 'stock' => 234, 'image' => 'monopoly.jpg'],
             ['id' => 59, 'name' => 'Scrabble Oyunu', 'category' => 'Oyun', 'price' => 199.99, 'brand' => 'Hasbro', 'rating' => 4.4, 'stock' => 189, 'image' => 'scrabble.jpg'],
             ['id' => 60, 'name' => 'Risk Strateji Oyunu', 'category' => 'Oyun', 'price' => 249.99, 'brand' => 'Hasbro', 'rating' => 4.3, 'stock' => 156, 'image' => 'risk.jpg'],
             
             // Otomotiv
             ['id' => 61, 'name' => 'Michelin Lastik Seti (4 Adet)', 'category' => 'Lastik', 'price' => 3999.99, 'brand' => 'Michelin', 'rating' => 4.7, 'stock' => 67, 'image' => 'michelin-lastik.jpg'],
             ['id' => 62, 'name' => 'Bosch Akü', 'category' => 'Akü', 'price' => 899.99, 'brand' => 'Bosch', 'rating' => 4.6, 'stock' => 123, 'image' => 'bosch-aku.jpg'],
             ['id' => 63, 'name' => 'Castrol Motor Yağı', 'category' => 'Motor Yağı', 'price' => 199.99, 'brand' => 'Castrol', 'rating' => 4.5, 'stock' => 456, 'image' => 'castrol-yag.jpg'],
             ['id' => 64, 'name' => 'Mobil 1 Motor Yağı', 'category' => 'Motor Yağı', 'price' => 249.99, 'brand' => 'Mobil', 'rating' => 4.6, 'stock' => 345, 'image' => 'mobil1-yag.jpg'],
             ['id' => 65, 'name' => 'Shell Helix Motor Yağı', 'category' => 'Motor Yağı', 'price' => 179.99, 'brand' => 'Shell', 'rating' => 4.4, 'stock' => 567, 'image' => 'shell-helix.jpg'],
             ['id' => 66, 'name' => 'Bridgestone Lastik', 'category' => 'Lastik', 'price' => 899.99, 'brand' => 'Bridgestone', 'rating' => 4.5, 'stock' => 234, 'image' => 'bridgestone-lastik.jpg'],
             ['id' => 67, 'name' => 'Continental Lastik', 'category' => 'Lastik', 'price' => 799.99, 'brand' => 'Continental', 'rating' => 4.6, 'stock' => 189, 'image' => 'continental-lastik.jpg'],
             ['id' => 68, 'name' => 'Goodyear Lastik', 'category' => 'Lastik', 'price' => 849.99, 'brand' => 'Goodyear', 'rating' => 4.5, 'stock' => 156, 'image' => 'goodyear-lastik.jpg'],
             ['id' => 69, 'name' => 'Pirelli Lastik', 'category' => 'Lastik', 'price' => 949.99, 'brand' => 'Pirelli', 'rating' => 4.7, 'stock' => 98, 'image' => 'pirelli-lastik.jpg'],
             ['id' => 70, 'name' => 'Dunlop Lastik', 'category' => 'Lastik', 'price' => 699.99, 'brand' => 'Dunlop', 'rating' => 4.4, 'stock' => 234, 'image' => 'dunlop-lastik.jpg'],
             
             // Sağlık & İlaç
             ['id' => 71, 'name' => 'Parol Tablet', 'category' => 'Ağrı Kesici', 'price' => 29.99, 'brand' => 'Sanofi', 'rating' => 4.3, 'stock' => 789, 'image' => 'parol-tablet.jpg'],
             ['id' => 72, 'name' => 'Aspirin Tablet', 'category' => 'Ağrı Kesici', 'price' => 19.99, 'brand' => 'Bayer', 'rating' => 4.2, 'stock' => 1234, 'image' => 'aspirin-tablet.jpg'],
             ['id' => 73, 'name' => 'B12 Vitamini', 'category' => 'Vitamin', 'price' => 89.99, 'brand' => 'Solgar', 'rating' => 4.5, 'stock' => 456, 'image' => 'b12-vitamin.jpg'],
             ['id' => 74, 'name' => 'D Vitamini', 'category' => 'Vitamin', 'price' => 79.99, 'brand' => 'Solgar', 'rating' => 4.4, 'stock' => 567, 'image' => 'd-vitamin.jpg'],
             ['id' => 75, 'name' => 'Omega 3', 'category' => 'Vitamin', 'price' => 129.99, 'brand' => 'Solgar', 'rating' => 4.6, 'stock' => 234, 'image' => 'omega3.jpg'],
             ['id' => 76, 'name' => 'C Vitamini', 'category' => 'Vitamin', 'price' => 69.99, 'brand' => 'Solgar', 'rating' => 4.3, 'stock' => 678, 'image' => 'c-vitamin.jpg'],
             ['id' => 77, 'name' => 'Magnezyum', 'category' => 'Mineral', 'price' => 99.99, 'brand' => 'Solgar', 'rating' => 4.4, 'stock' => 345, 'image' => 'magnezyum.jpg'],
             ['id' => 78, 'name' => 'Çinko', 'category' => 'Mineral', 'price' => 89.99, 'brand' => 'Solgar', 'rating' => 4.3, 'stock' => 456, 'image' => 'cinko.jpg'],
             ['id' => 79, 'name' => 'Demir', 'category' => 'Mineral', 'price' => 79.99, 'brand' => 'Solgar', 'rating' => 4.2, 'stock' => 567, 'image' => 'demir.jpg'],
             ['id' => 80, 'name' => 'Kalsiyum', 'category' => 'Mineral', 'price' => 99.99, 'brand' => 'Solgar', 'rating' => 4.4, 'stock' => 234, 'image' => 'kalsiyum.jpg'],
             
             // Bahçe & Outdoor
             ['id' => 81, 'name' => 'Gardena Tırmık', 'category' => 'Bahçe Aleti', 'price' => 199.99, 'brand' => 'Gardena', 'rating' => 4.4, 'stock' => 123, 'image' => 'gardena-tirmik.jpg'],
             ['id' => 82, 'name' => 'Fiskars Makas', 'category' => 'Bahçe Aleti', 'price' => 149.99, 'brand' => 'Fiskars', 'rating' => 4.6, 'stock' => 89, 'image' => 'fiskars-makas.jpg'],
             ['id' => 83, 'name' => 'Bosch Çim Biçme Makinesi', 'category' => 'Bahçe Makinesi', 'price' => 2999.99, 'brand' => 'Bosch', 'rating' => 4.5, 'stock' => 34, 'image' => 'bosch-cim-bicme.jpg'],
             ['id' => 84, 'name' => 'Makita Zincirli Testere', 'category' => 'Bahçe Makinesi', 'price' => 3999.99, 'brand' => 'Makita', 'rating' => 4.7, 'stock' => 23, 'image' => 'makita-testere.jpg'],
             ['id' => 85, 'name' => 'DeWalt Matkap', 'category' => 'El Aleti', 'price' => 1499.99, 'brand' => 'DeWalt', 'rating' => 4.6, 'stock' => 67, 'image' => 'dewalt-matkap.jpg'],
             ['id' => 86, 'name' => 'Milwaukee Tornavida', 'category' => 'El Aleti', 'price' => 299.99, 'brand' => 'Milwaukee', 'rating' => 4.5, 'stock' => 234, 'image' => 'milwaukee-tornavida.jpg'],
             ['id' => 87, 'name' => 'Stanley Çekiç', 'category' => 'El Aleti', 'price' => 199.99, 'brand' => 'Stanley', 'rating' => 4.4, 'stock' => 345, 'image' => 'stanley-cekic.jpg'],
             ['id' => 88, 'name' => 'Knipex Pense', 'category' => 'El Aleti', 'price' => 399.99, 'brand' => 'Knipex', 'rating' => 4.7, 'stock' => 123, 'image' => 'knipex-pense.jpg'],
             ['id' => 89, 'name' => 'Wera Tornavida Seti', 'category' => 'El Aleti', 'price' => 899.99, 'brand' => 'Wera', 'rating' => 4.8, 'stock' => 67, 'image' => 'wera-tornavida.jpg'],
             ['id' => 90, 'name' => 'Hilti Kırıcı', 'category' => 'El Aleti', 'price' => 5999.99, 'brand' => 'Hilti', 'rating' => 4.9, 'stock' => 12, 'image' => 'hilti-kirici.jpg'],
             
             // Pet Shop
             ['id' => 91, 'name' => 'Royal Canin Kedi Maması', 'category' => 'Kedi Maması', 'price' => 299.99, 'brand' => 'Royal Canin', 'rating' => 4.6, 'stock' => 234, 'image' => 'royal-canin-kedi.jpg'],
             ['id' => 92, 'name' => 'Acana Köpek Maması', 'category' => 'Köpek Maması', 'price' => 399.99, 'brand' => 'Acana', 'rating' => 4.7, 'stock' => 189, 'image' => 'acana-kopek.jpg'],
             ['id' => 93, 'name' => 'Hill\'s Science Diet', 'category' => 'Kedi Maması', 'price' => 349.99, 'brand' => 'Hill\'s', 'rating' => 4.5, 'stock' => 156, 'image' => 'hills-science.jpg'],
             ['id' => 94, 'name' => 'Purina Pro Plan', 'category' => 'Köpek Maması', 'price' => 449.99, 'brand' => 'Purina', 'rating' => 4.4, 'stock' => 123, 'image' => 'purina-proplan.jpg'],
             ['id' => 95, 'name' => 'Orijen Kedi Maması', 'category' => 'Kedi Maması', 'price' => 599.99, 'brand' => 'Orijen', 'rating' => 4.8, 'stock' => 89, 'image' => 'orijen-kedi.jpg'],
             ['id' => 96, 'name' => 'Taste of the Wild', 'category' => 'Köpek Maması', 'price' => 499.99, 'brand' => 'Taste of the Wild', 'rating' => 4.6, 'stock' => 78, 'image' => 'totw-kopek.jpg'],
             ['id' => 97, 'name' => 'Wellness Core', 'category' => 'Kedi Maması', 'price' => 399.99, 'brand' => 'Wellness', 'rating' => 4.5, 'stock' => 134, 'image' => 'wellness-core.jpg'],
             ['id' => 98, 'name' => 'Blue Buffalo', 'category' => 'Köpek Maması', 'price' => 549.99, 'brand' => 'Blue Buffalo', 'rating' => 4.7, 'stock' => 67, 'image' => 'blue-buffalo.jpg'],
             ['id' => 99, 'name' => 'NutriSource', 'category' => 'Kedi Maması', 'price' => 379.99, 'brand' => 'NutriSource', 'rating' => 4.4, 'stock' => 189, 'image' => 'nutrisource-kedi.jpg'],
             ['id' => 100, 'name' => 'Fromm Family', 'category' => 'Köpek Maması', 'price' => 479.99, 'brand' => 'Fromm', 'rating' => 4.6, 'stock' => 98, 'image' => 'fromm-family.jpg']
         ];
     }
     
     public function getAllProducts() {
         return $this->products;
     }
     
     public function getProductsByCategory($category) {
         return array_filter($this->products, function($product) use ($category) {
             return strtolower($product['category']) === strtolower($category);
         });
     }
     
     public function getProductsByBrand($brand) {
         return array_filter($this->products, function($product) use ($brand) {
             return strtolower($product['brand']) === strtolower($brand);
         });
     }
     
     public function getProductsByPriceRange($minPrice, $maxPrice) {
         return array_filter($this->products, function($product) use ($minPrice, $maxPrice) {
             return $product['price'] >= $minPrice && $product['price'] <= $maxPrice;
         });
     }
     
     public function searchProducts($query) {
         return array_filter($this->products, function($product) use ($query) {
             $searchTerm = strtolower($query);
             return strpos(strtolower($product['name']), $searchTerm) !== false ||
                    strpos(strtolower($product['brand']), $searchTerm) !== false ||
                    strpos(strtolower($product['category']), $searchTerm) !== false;
         });
     }
     
     public function getProductById($id) {
         foreach ($this->products as $product) {
             if ($product['id'] == $id) {
                 return $product;
             }
         }
         return null;
     }
     
     public function getTopRatedProducts($limit = 10) {
         usort($this->products, function($a, $b) {
             return $b['rating'] <=> $a['rating'];
         });
         return array_slice($this->products, 0, $limit);
     }
     
     public function getLowStockProducts($threshold = 50) {
         return array_filter($this->products, function($product) use ($threshold) {
             return $product['stock'] <= $threshold;
         });
     }
     
     /**
      * Tüm ürün kategorilerini analiz et ve istatistiklerini döndür
      */
     public function getCategoryAnalysis() {
         $categories = [];
         $totalProducts = count($this->products);
         
         foreach ($this->products as $product) {
             $category = $product['category'];
             
             if (!isset($categories[$category])) {
                 $categories[$category] = [
                     'name' => $category,
                     'product_count' => 0,
                     'total_price' => 0,
                     'avg_price' => 0,
                     'avg_rating' => 0,
                     'total_rating' => 0,
                     'total_stock' => 0,
                     'products' => [],
                     'price_range' => [
                         'min' => PHP_FLOAT_MAX,
                         'max' => 0
                     ],
                     'top_rated_products' => [],
                     'best_sellers' => []
                 ];
             }
             
             $categories[$category]['product_count']++;
             $categories[$category]['total_price'] += $product['price'];
             $categories[$category]['total_rating'] += $product['rating'];
             $categories[$category]['total_stock'] += $product['stock'];
             $categories[$category]['products'][] = $product;
             
             // Fiyat aralığı güncelle
             if ($product['price'] < $categories[$category]['price_range']['min']) {
                 $categories[$category]['price_range']['min'] = $product['price'];
             }
             if ($product['price'] > $categories[$category]['price_range']['max']) {
                 $categories[$category]['price_range']['max'] = $product['price'];
             }
         }
         
         // Ortalama değerleri hesapla ve en iyi ürünleri belirle
         foreach ($categories as $categoryName => &$categoryData) {
             $categoryData['avg_price'] = round($categoryData['total_price'] / $categoryData['product_count'], 2);
             $categoryData['avg_rating'] = round($categoryData['total_rating'] / $categoryData['product_count'], 1);
             $categoryData['market_share'] = round(($categoryData['product_count'] / $totalProducts) * 100, 1);
             
             // En yüksek puanlı ürünleri al
             usort($categoryData['products'], function($a, $b) {
                 return $b['rating'] <=> $a['rating'];
             });
             $categoryData['top_rated_products'] = array_slice($categoryData['products'], 0, 3);
             
             // En çok stokta olan ürünleri al (best seller göstergesi)
             usort($categoryData['products'], function($a, $b) {
                 return $b['stock'] <=> $a['stock'];
             });
             $categoryData['best_sellers'] = array_slice($categoryData['products'], 0, 3);
         }
         
         return $categories;
     }
     
     /**
      * Kategoriye göre öneriler oluştur
      */
     public function getCategoryRecommendations($limit = 5) {
         $categories = $this->getCategoryAnalysis();
         $recommendations = [];
         
         // Kategorileri ürün sayısına göre sırala
         uasort($categories, function($a, $b) {
             return $b['product_count'] <=> $a['product_count'];
         });
         
         foreach ($categories as $categoryName => $categoryData) {
             $recommendations[] = [
                 'category' => $categoryName,
                 'product_count' => $categoryData['product_count'],
                 'avg_price' => $categoryData['avg_price'],
                 'avg_rating' => $categoryData['avg_rating'],
                 'market_share' => $categoryData['market_share'],
                 'price_range' => $categoryData['price_range'],
                 'top_products' => array_map(function($product) {
                     return [
                         'id' => $product['id'],
                         'name' => $product['name'],
                         'price' => $product['price'],
                         'rating' => $product['rating'],
                         'brand' => $product['brand']
                     ];
                 }, $categoryData['top_rated_products']),
                 'best_sellers' => array_map(function($product) {
                     return [
                         'id' => $product['id'],
                         'name' => $product['name'],
                         'price' => $product['price'],
                         'stock' => $product['stock'],
                         'brand' => $product['brand']
                     ];
                 }, $categoryData['best_sellers'])
             ];
         }
         
         return array_slice($recommendations, 0, $limit);
     }
     
     /**
      * Belirli bir kategori için detaylı analiz
      */
     public function getCategoryDetails($categoryName) {
         $categories = $this->getCategoryAnalysis();
         
         if (!isset($categories[$categoryName])) {
             return null;
         }
         
         $categoryData = $categories[$categoryName];
         
         // Kategori içindeki ürünleri fiyata göre sırala
         usort($categoryData['products'], function($a, $b) {
             return $a['price'] <=> $b['price'];
         });
         
         return [
             'category' => $categoryName,
             'summary' => [
                 'product_count' => $categoryData['product_count'],
                 'avg_price' => $categoryData['avg_price'],
                 'avg_rating' => $categoryData['avg_rating'],
                 'total_stock' => $categoryData['total_stock'],
                 'market_share' => $categoryData['market_share']
             ],
             'price_analysis' => [
                 'range' => $categoryData['price_range'],
                 'budget_options' => array_slice($categoryData['products'], 0, 3), // En ucuz 3 ürün
                 'premium_options' => array_slice(array_reverse($categoryData['products']), 0, 3) // En pahalı 3 ürün
             ],
             'quality_analysis' => [
                 'top_rated' => $categoryData['top_rated_products'],
                 'best_sellers' => $categoryData['best_sellers']
             ],
             'brand_distribution' => $this->getBrandDistribution($categoryData['products']),
             'all_products' => array_map(function($product) {
                 return [
                     'id' => $product['id'],
                     'name' => $product['name'],
                     'price' => $product['price'],
                     'rating' => $product['rating'],
                     'brand' => $product['brand'],
                     'stock' => $product['stock']
                 ];
             }, $categoryData['products'])
         ];
     }
     
     /**
      * Kategori içindeki marka dağılımını hesapla
      */
     private function getBrandDistribution($products) {
         $brands = [];
         
         foreach ($products as $product) {
             $brand = $product['brand'];
             if (!isset($brands[$brand])) {
                 $brands[$brand] = [
                     'name' => $brand,
                     'count' => 0,
                     'avg_price' => 0,
                     'avg_rating' => 0,
                     'total_price' => 0,
                     'total_rating' => 0
                 ];
             }
             
             $brands[$brand]['count']++;
             $brands[$brand]['total_price'] += $product['price'];
             $brands[$brand]['total_rating'] += $product['rating'];
         }
         
         // Ortalama değerleri hesapla
         foreach ($brands as &$brand) {
             $brand['avg_price'] = round($brand['total_price'] / $brand['count'], 2);
             $brand['avg_rating'] = round($brand['total_rating'] / $brand['count'], 1);
         }
         
         // Marka sayısına göre sırala
         uasort($brands, function($a, $b) {
             return $b['count'] <=> $a['count'];
         });
         
         return $brands;
     }
 }