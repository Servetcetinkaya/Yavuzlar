package main

import (
	"fmt"
	"os"
	"time"
)

type User struct {
	Username string
	Password string
	IsAdmin  bool
}

var users = map[string]*User{
	"admin": {Username: "admin", Password: "admin123", IsAdmin: true},
}

func addCustomer(username, password string) {
	if _, exists := users[username]; exists {
		fmt.Println("Müşteri zaten mevcut.")
	} else {
		users[username] = &User{Username: username, Password: password, IsAdmin: false}
		logAction(fmt.Sprintf("Müşteri eklendi: %s", username))
		fmt.Println("Müşteri başarıyla eklendi.")
	}
}

func deleteCustomer(username string) {
	if _, exists := users[username]; exists {
		delete(users, username)
		logAction(fmt.Sprintf("Müşteri silindi: %s", username))
		fmt.Println("Müşteri başarıyla silindi.")
	} else {
		fmt.Println("Müşteri mevcut değil.")
	}
}

func showLogs() {
	file, err := os.ReadFile("logs.txt")
	if err != nil {
		fmt.Println("Log dosyasını okumada bir hata oluştu:", err)
		return
	}
	fmt.Println("Log verileri:")
	fmt.Println(string(file))
}

func logAction(action string) {
	file, err := os.OpenFile("logs.txt", os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0644)
	if err != nil {
		fmt.Println("Log dosyasını açmada bir hata oluştu:", err)
		return
	}
	defer file.Close()

	logEntry := fmt.Sprintf("%s: %s\n", time.Now().Format(time.RFC3339), action)
	if _, err := file.WriteString(logEntry); err != nil {
		fmt.Println("Log kaydı yazmada bir hata oluştu:", err)
	}
}
