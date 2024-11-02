package main

import (
	"fmt"
)

func adminMenu() {
	for {
		fmt.Println("\nAdmin Menü")
		fmt.Println("1. Müşteri Ekle")
		fmt.Println("2. Müşteri Sil")
		fmt.Println("3. Logları Göster")
		fmt.Println("4. Çıkış Yap")
		fmt.Print("Bir seçenek girin: ")

		var choice int
		fmt.Scanln(&choice)

		switch choice {
		case 1:
			var username, password string
			fmt.Print("Yeni müşteri kullanıcı adını girin: ")
			fmt.Scanln(&username)
			fmt.Print("Yeni müşteri şifresini girin: ")
			fmt.Scanln(&password)
			addCustomer(username, password)
		case 2:
			var username string
			fmt.Print("Silinecek kullanıcı adını girin: ")
			fmt.Scanln(&username)
			deleteCustomer(username)
		case 3:
			showLogs()
		case 4:
			fmt.Println("Çıkış yapılıyor...")
			return
		default:
			fmt.Println("Geçersiz seçenek. Tekrar deneyin.")
		}
	}
}

func customerMenu(user *User) {
	for {
		fmt.Printf("\nHoş geldiniz, %s!\n", user.Username)
		fmt.Println("1. Profilimi Görüntüle")
		fmt.Println("2. Şifre Değiştir")
		fmt.Println("3. Çıkış Yap")
		fmt.Print("Bir seçenek girin: ")

		var choice int
		fmt.Scanln(&choice)

		switch choice {
		case 1:
			fmt.Printf("Kullanıcı Adı: %s\nRol: %s\n", user.Username, role(user))
		case 2:
			var newPassword string
			fmt.Print("Yeni şifreyi girin: ")
			fmt.Scanln(&newPassword)
			user.Password = newPassword
			fmt.Println("Şifre başarıyla değiştirildi.")
		case 3:
			fmt.Println("Çıkış yapılıyor...")
			return
		default:
			fmt.Println("Geçersiz seçenek. Tekrar deneyin.")
		}
	}
}

func role(user *User) string {
	if user.IsAdmin {
		return "Admin"
	}
	return "Müşteri"
}
