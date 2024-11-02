package main

import "fmt"

func main() {
	for {
		var username, password string
		fmt.Print("Kullanıcı Adı: ")
		fmt.Scanln(&username)
		fmt.Print("Şifre: ")
		fmt.Scanln(&password)

		user := login(username, password)
		if user != nil {
			if user.IsAdmin {
				adminMenu()
			} else {
				customerMenu(user)
			}
		}
	}
}

func login(username, password string) *User {
	if user, exists := users[username]; exists && user.Password == password {
		fmt.Println("Giriş başarılı.")
		return user
	}
	fmt.Println("Geçersiz kullanıcı adı veya şifre.")
	return nil
}
