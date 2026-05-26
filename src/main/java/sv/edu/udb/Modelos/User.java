package sv.edu.udb.Modelos;

import jakarta.persistence.*;
import lombok.Data;

@Entity
@Table
@Data
public class User {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Integer idUser;
    private String username;
    private String firstname;
    private String lastname;
    private Integer age;
    private String password;
}
